<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Checkup;
use App\Models\DoctorSession;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\DoctorSessionService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private DoctorSessionService $sessionService) {}

    public function index(Request $request)
    {
        $sessions = DoctorSession::with(['doctor', 'createdByAdmin', 'parentSession'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->doctor_id, fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->search, fn($q, $s) =>
                $q->where('session_code', 'like', "%{$s}%")
                  ->orWhere('school_name', 'like', "%{$s}%")
            )
            ->latest()
            ->paginate(20);

        $doctors = User::role('doctor')->where('is_active', true)->get();

        return view('admin.sessions.index', compact('sessions', 'doctors'));
    }

    public function create()
    {
        $doctors = User::role('doctor')->where('is_active', true)->orderBy('name')->get();
        $schools = School::where('is_active', true)->orderBy('name')->get();
        return view('admin.sessions.create', compact('doctors', 'schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'doctor_id'        => 'required|exists:users,id',
            'school_id'        => 'required|exists:schools,id',
            'school_name'      => 'required|string|max:255',
            'school_city'      => 'nullable|string|max:100',
            'classes_assigned' => 'nullable|array',
            'visit_date'       => 'required|date|after_or_equal:today',
            'admin_notes'      => 'nullable|string|max:1000',
        ]);

        // Warn if this doctor already has a pending/active session for the SAME school
        $doctor = User::findOrFail($data['doctor_id']);
        $conflict = $doctor->doctorSessions()
            ->where('school_name', $data['school_name'])
            ->whereIn('status', ['pending', 'active'])
            ->where('expires_at', '>', now())
            ->exists();

        if ($conflict) {
            return back()->withErrors(['school_id' => 'This doctor already has an active/pending session for this school. Revoke it first or wait for it to expire.'])->withInput();
        }

        $session = $this->sessionService->createSession($data, auth()->user());

        return redirect()->route('admin.sessions.show', $session)
                         ->with('success', "Session created! Code: {$session->session_code}");
    }

    public function show(DoctorSession $session)
    {
        $session->load(['doctor', 'createdByAdmin', 'parentSession', 'childSessions.doctor', 'checkups.student', 'activityLogs.user']);

        $logs = ActivityLog::with('user')
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
            'visit_date'  => 'required|date|after_or_equal:today',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $newSession = $this->sessionService->reopenSession($session, $data, auth()->user());

        return redirect()->route('admin.sessions.show', $newSession)
                         ->with('success', "New session created: {$newSession->session_code}. Share this new code with Dr. {$session->doctor->name}.");
    }

    public function alerts(Request $request)
    {
        $query = Checkup::with(['student', 'doctorSession'])
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

        $allCheckups = Checkup::completed()->whereNotNull('alerts')->where('alerts', '!=', '[]')->get();
        $allAlerts   = $allCheckups->flatMap(fn($c) => $c->alerts ?? []);

        $totalAlerts     = $allAlerts->count();
        $criticalAlerts  = $allAlerts->filter(fn($a) => str_contains($a, 'CRITICAL'))->count();
        $studentsAffected = $allCheckups->pluck('student_id')->unique()->count();

        $schools = Student::distinct('school_name')->pluck('school_name')->sort()->values();

        return view('admin.alerts', compact('checkups', 'totalAlerts', 'criticalAlerts', 'studentsAffected', 'schools'));
    }

    public function logs(Request $request)
    {
        $logs = ActivityLog::with(['user', 'doctorSession'])
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->session_id, fn($q) => $q->where('doctor_session_id', $request->session_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(30);

        $sessions = DoctorSession::with('doctor')->latest()->take(50)->get();
        $users    = User::orderBy('name')->get();
        $actions  = ActivityLog::distinct('action')->pluck('action')->sort()->values();

        return view('admin.logs', compact('logs', 'sessions', 'users', 'actions'));
    }
}
