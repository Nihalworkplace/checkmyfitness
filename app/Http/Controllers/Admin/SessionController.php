<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Checkup;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\DoctorSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function __construct(private DoctorSessionService $sessionService) {}

    public function index(Request $request)
    {
        $sessions = DoctorSession::with(['doctor', 'createdByAdmin', 'parentSession'])
            ->where('created_by', Auth::id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->search, fn($q, $s) =>
                $q->where('session_code', 'like', "%{$s}%")
                  ->orWhere('school_name', 'like', "%{$s}%")
            )
            ->latest()
            ->paginate(20);

        $doctors = Doctor::where('admin_id', Auth::id())->where('is_active', true)->get();

        return view('admin.sessions.index', compact('sessions', 'doctors'));
    }

    public function create()
    {
        $doctors = Doctor::where('admin_id', Auth::id())->where('is_active', true)->orderBy('name')->get();
        $schools = School::where('admin_id', Auth::id())->where('is_active', true)->orderBy('name')->get();

        $defaults  = ['1A','1B','2A','2B','3A','3B','4A','4B','5A','5B','6A','6B',
                      '7A','7B','8A','8B','9A','9B','10A','10B','11A','11B','12A','12B'];
        $dbClasses = Student::distinct()->orderBy('class_section')->pluck('class_section')->toArray();
        $classes   = collect(array_unique(array_merge($defaults, $dbClasses)))->sort()->values();

        return view('admin.sessions.create', compact('doctors', 'schools', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'doctor_ids'          => 'required|array|min:1',
            'doctor_ids.*'        => 'exists:doctors,id',
            'school_id'           => 'required|exists:schools,id',
            'school_name'         => 'required|string|max:255',
            'school_city'         => 'nullable|string|max:100',
            'classes_assigned'    => 'nullable|array',
            'session_start_date'  => 'required|date',
            'session_start_time'  => 'required|date_format:H:i',
            'admin_notes'         => 'nullable|string|max:1000',
        ]);

        $displayTz = config('app.display_timezone');
        $startsAt  = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['session_start_date'] . ' ' . $data['session_start_time'],
            $displayTz
        )->utc();

        if ($startsAt->copy()->addMinutes(5)->isPast()) {
            return back()
                ->withErrors(['session_start_date' => 'Session start time cannot be in the past.'])
                ->withInput();
        }

        $data['starts_at'] = $startsAt;

        $created = [];
        $skipped = [];

        foreach ($data['doctor_ids'] as $doctorId) {
            $doctor = Doctor::findOrFail($doctorId);

            $conflict = $doctor->doctorSessions()
                ->where('school_name', $data['school_name'])
                ->whereIn('status', ['pending', 'active'])
                ->where('expires_at', '>', now())
                ->exists();

            if ($conflict) {
                $skipped[] = "Dr. {$doctor->name} — already has an active/pending session for this school.";
                continue;
            }

            $session = $this->sessionService->createSession(
                array_merge($data, ['doctor_id' => $doctorId]),
                auth()->user()
            );

            $created[] = "Dr. {$doctor->name} ({$session->session_code})";
        }

        if (empty($created)) {
            return back()
                ->withErrors(['doctor_ids' => 'No sessions created. All selected doctors already have active sessions for this school.'])
                ->withInput();
        }

        $msg = 'Sessions created: ' . implode(', ', $created);
        if ($skipped) {
            $msg .= ' | Skipped: ' . implode('; ', $skipped);
        }

        return redirect()->route('admin.sessions.index')->with('success', $msg);
    }

    public function show(DoctorSession $session)
    {
        $session->load(['doctor', 'createdByAdmin', 'parentSession', 'childSessions.doctor', 'checkups.student', 'activityLogs.actor']);

        $logs = ActivityLog::with('actor')
                           ->where('doctor_session_id', $session->id)
                           ->latest()
                           ->paginate(30);

        return view('admin.sessions.show', compact('session', 'logs'));
    }

    public function revoke(DoctorSession $session)
    {
        if (! in_array($session->status, ['active', 'pending'])) {
            return back()->withErrors(['error' => 'Only active or pending sessions can be revoked.']);
        }

        $this->sessionService->revokeSession($session, auth()->user());

        return back()->with('success', "Session {$session->session_code} has been revoked. Doctor will be logged out immediately.");
    }

    public function reopen(Request $request, DoctorSession $session)
    {
        $data = $request->validate([
            'session_start_date' => 'required|date',
            'session_start_time' => 'required|date_format:H:i',
            'admin_notes'        => 'nullable|string|max:1000',
        ]);

        $displayTz = config('app.display_timezone');
        $startsAt  = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['session_start_date'] . ' ' . $data['session_start_time'],
            $displayTz
        )->utc();

        if ($startsAt->copy()->addMinutes(5)->isPast()) {
            return back()->withErrors(['session_start_date' => 'Session start time cannot be in the past.']);
        }

        $data['starts_at'] = $startsAt;

        $newSession = $this->sessionService->reopenSession($session, $data, auth()->user());

        return redirect()->route('admin.sessions.show', $newSession)
                         ->with('success', "New session created: {$newSession->session_code}. Share this new code with Dr. {$session->doctor->name}.");
    }

    public function alerts(Request $request)
    {
        $query = Checkup::with(['student', 'doctorSession'])
            ->whereHas('doctor', fn($q) => $q->where('admin_id', Auth::id()))
            ->completed()
            ->whereNotNull('alerts')
            ->where('alerts', '!=', '[]')
            ->when($request->search, fn($q, $s) =>
                $q->whereHas('student', fn($sq) => $sq->where('name', 'like', "%{$s}%"))
            )
            ->when($request->school, fn($q, $s) =>
                $q->whereHas('student', fn($sq) => $sq->where('school_name', $s))
            )
            ->when($request->critical, fn($q) =>
                $q->where('alerts', 'like', '%CRITICAL%')
            )
            ->latest('checkup_date');

        $checkups = $query->paginate(25)->withQueryString();

        $allCheckups = Checkup::whereHas('doctor', fn($q) => $q->where('admin_id', Auth::id()))
            ->completed()->whereNotNull('alerts')->where('alerts', '!=', '[]')->get();
        $allAlerts = $allCheckups->flatMap(fn($c) => $c->alerts ?? []);

        $totalAlerts      = $allAlerts->count();
        $criticalAlerts   = $allAlerts->filter(fn($a) => str_contains($a, 'CRITICAL'))->count();
        $studentsAffected = $allCheckups->pluck('student_id')->unique()->count();

        $schools = Student::where('admin_id', Auth::id())->distinct('school_name')->pluck('school_name')->sort()->values();

        return view('admin.alerts', compact('checkups', 'totalAlerts', 'criticalAlerts', 'studentsAffected', 'schools'));
    }

    public function logs(Request $request)
    {
        $logs = ActivityLog::with(['actor', 'doctorSession'])
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->session_id, fn($q) => $q->where('doctor_session_id', $request->session_id))
            ->latest()
            ->paginate(30);

        $sessions = DoctorSession::with('doctor')->where('created_by', Auth::id())->latest()->take(50)->get();
        $actions  = ActivityLog::distinct('action')->pluck('action')->sort()->values();

        return view('admin.logs', compact('logs', 'sessions', 'actions'));
    }
}
