<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Checkup;
use App\Models\DoctorSession;
use App\Models\Student;
use App\Services\DoctorSessionService;
use Illuminate\Http\Request;

class CheckupController extends Controller
{
    public function __construct(private DoctorSessionService $sessionService) {}

    /**
     * Active session overview — doctor's main screen.
     */
    public function activeSession()
    {
        $doctorSession = app('doctor_session');
        $doctor        = auth()->user();

        $students = Student::where('school_name', $doctorSession->school_name)
            ->when($doctorSession->classes_assigned, fn($q) =>
                $q->whereIn('class_section', $doctorSession->classes_assigned)
            )
            ->with(['checkups' => fn($q) =>
                $q->where('doctor_session_id', $doctorSession->id)
            ])
            ->orderBy('class_section')
            ->orderBy('name')
            ->get();

        $completedIds = $students->flatMap->checkups
            ->where('status', 'completed')
            ->pluck('student_id')
            ->unique();

        $this->sessionService->log($doctor, $doctorSession, 'view_dashboard', 'Doctor viewed active session dashboard');

        return view('doctor.session', compact('doctorSession', 'students', 'completedIds'));
    }

    /**
     * Show checkup form for a specific student.
     */
    public function showForm(Student $student)
    {
        $doctorSession = app('doctor_session');

        // Load existing draft or completed checkup for this session
        $checkup = Checkup::where('student_id', $student->id)
                          ->where('doctor_session_id', $doctorSession->id)
                          ->first();

        $this->sessionService->log(auth()->user(), $doctorSession, 'view_student',
            "Opened checkup form for {$student->name}", ['student_id' => $student->id]);

        return view('doctor.checkup-form', compact('student', 'checkup', 'doctorSession'));
    }

    /**
     * Save (draft) or complete a checkup.
     */
    public function saveCheckup(Request $request, Student $student)
    {
        $doctorSession = app('doctor_session');
        $doctor        = auth()->user();

        $data = $request->validate([
            'status'             => 'required|in:draft,completed',
            // Physical
            'height_cm'          => 'nullable|numeric|min:50|max:250',
            'weight_kg'          => 'nullable|numeric|min:10|max:200',
            'heart_rate_bpm'     => 'nullable|integer|min:30|max:220',
            'bp_systolic'        => 'nullable|string|max:5',
            'bp_diastolic'       => 'nullable|string|max:5',
            'temperature_f'      => 'nullable|numeric|min:90|max:110',
            'spo2_percent'       => 'nullable|integer|min:70|max:100',
            // Sensory
            'vision_left'        => 'nullable|string|max:10',
            'vision_right'       => 'nullable|string|max:10',
            'hearing'            => 'nullable|in:Normal,Mild Issue,Needs Test',
            'dental_score'       => 'nullable|integer|min:1|max:10',
            'eye_strain'         => 'nullable|in:None,Mild,Severe',
            // Lab
            'haemoglobin_gdl'    => 'nullable|numeric|min:1|max:25',
            'vitamin_d_ngml'     => 'nullable|numeric|min:1|max:150',
            'iron_level'         => 'nullable|in:Normal,Low,Very Low',
            'blood_sugar_mgdl'   => 'nullable|numeric|min:30|max:500',
            // Musculoskeletal
            'posture'            => 'nullable|in:Good,Mild Curve,Scoliosis Risk',
            'grip_strength_score'=> 'nullable|integer|min:1|max:10',
            'flexibility'        => 'nullable|in:Good,Average,Poor',
            'flat_feet'          => 'nullable|in:None,Mild,Moderate',
            // Mental
            'mental_score'       => 'nullable|integer|min:1|max:10',
            'stress_level'       => 'nullable|in:Low,Moderate,High',
            'sleep_quality'      => 'nullable|in:Good,Average,Poor',
            // Skin
            'skin_health'        => 'nullable|in:Healthy,Mild Issue,Needs Attention',
            'hair_health'        => 'nullable|in:Healthy,Mild Issue,Needs Attention',
            // Notes
            'doctor_notes'       => 'nullable|string|max:2000',
            'recommendations'    => 'nullable|string|max:2000',
        ]);

        // Auto-calculate BMI
        if (! empty($data['height_cm']) && ! empty($data['weight_kg'])) {
            $heightM     = $data['height_cm'] / 100;
            $data['bmi'] = round($data['weight_kg'] / ($heightM * $heightM), 1);
        }

        $data['doctor_id']        = $doctor->id;
        $data['doctor_session_id']= $doctorSession->id;
        $data['checkup_date']     = now()->toDateString();

        // Upsert — update existing or create new
        $existing = Checkup::where('student_id', $student->id)
                           ->where('doctor_session_id', $doctorSession->id)
                           ->first();

        if ($existing) {
            $old = $existing->toArray();
            $existing->update($data);
            $checkup = $existing->fresh();
            $action  = 'update_checkup';
            $oldVals = $old;
        } else {
            $checkup = Checkup::create(['student_id' => $student->id, ...$data]);
            $action  = 'create_checkup';
            $oldVals = null;
        }

        // Auto-calculate alerts and overall score when completing
        if ($data['status'] === 'completed') {
            $alerts       = $checkup->generateAlerts();
            $overallScore = $checkup->calculateOverallScore();
            $checkup->update([
                'alerts'        => $alerts,
                'overall_score' => $overallScore,
            ]);
        }

        $this->sessionService->log($doctor, $doctorSession, $action,
            "{$student->name}'s checkup " . ($data['status'] === 'completed' ? 'completed' : 'saved as draft'),
            ['student_id' => $student->id, 'checkup_id' => $checkup->id, 'status' => $data['status']],
        );

        $msg = $data['status'] === 'completed'
            ? "✓ {$student->name}'s checkup completed!"
            : "Draft saved for {$student->name}.";

        return redirect()->route('doctor.session.active')
                         ->with('success', $msg);
    }

    /**
     * Completed checkups for this session.
     */
    public function completed()
    {
        $doctorSession = app('doctor_session');

        $checkups = Checkup::with('student')
                           ->where('doctor_session_id', $doctorSession->id)
                           ->where('status', 'completed')
                           ->latest()
                           ->get();

        return view('doctor.completed', compact('checkups', 'doctorSession'));
    }

    /**
     * Session summary.
     */
    public function summary()
    {
        $doctorSession = app('doctor_session');

        $checkups = Checkup::with('student')
                           ->where('doctor_session_id', $doctorSession->id)
                           ->get();

        $logs = ActivityLog::where('doctor_session_id', $doctorSession->id)
                           ->latest()
                           ->get();

        $stats = [
            'completed'      => $checkups->where('status', 'completed')->count(),
            'drafts'         => $checkups->where('status', 'draft')->count(),
            'total_alerts'   => $checkups->flatMap(fn($c) => $c->alerts ?? [])->count(),
            'avg_score'      => $checkups->where('status', 'completed')->avg('overall_score'),
            'critical_alerts'=> $checkups->flatMap(fn($c) => $c->alerts ?? [])
                                         ->filter(fn($a) => str_contains($a, 'CRITICAL'))
                                         ->count(),
        ];

        return view('doctor.summary', compact('doctorSession', 'checkups', 'logs', 'stats'));
    }
}
