<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\DoctorSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private DoctorSessionService $sessionService) {}

    public function showLogin(Request $request)
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('doctor')->check()) {
            return redirect()->route('doctor.session.active');
        }
        if (Auth::guard('parent')->check()) {
            return redirect()->route('parent.dashboard');
        }

        $role = $request->get('role', 'parent');
        return view('auth.login', compact('role'));
    }

    // ── Admin Login ──────────────────────────────────────────
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $user = Auth::guard('web')->user();

        if (! $user->hasRole('admin')) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'Access denied. You are not authorised as an Admin.',
            ]);
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $this->sessionService->log($user, null, 'login', 'Admin logged in');
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))
                         ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    // ── Parent Login ─────────────────────────────────────────
    public function parentLogin(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:email,code',
        ]);

        if ($request->login_type === 'email') {
            return $this->parentEmailLogin($request);
        }

        return $this->parentCodeLogin($request);
    }

    private function parentEmailLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::guard('parent')->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $guardian = Auth::guard('parent')->user();

        if (! $guardian->is_active) {
            Auth::guard('parent')->logout();
            throw ValidationException::withMessages(['email' => 'Your account has been deactivated.']);
        }

        $this->sessionService->log($guardian, null, 'login', 'Parent logged in via email');
        $request->session()->regenerate();

        return redirect()->intended(route('parent.dashboard'))
                         ->with('success', 'Welcome back!');
    }

    private function parentCodeLogin(Request $request)
    {
        $request->validate([
            'reference_code' => 'required|string',
            'date_of_birth'  => 'required|date',
        ]);

        $code = strtoupper(trim($request->reference_code));
        $dob  = date('Y-m-d', strtotime($request->date_of_birth));

        $student = Student::where('reference_code', $code)
                          ->whereDate('date_of_birth', $dob)
                          ->first();

        if (! $student) {
            throw ValidationException::withMessages([
                'reference_code' => 'Reference code or date of birth is incorrect.',
            ]);
        }

        $guardian = Guardian::where('id', $student->parent_id)
                            ->where('is_active', true)
                            ->first();

        if (! $guardian) {
            throw ValidationException::withMessages([
                'reference_code' => 'No active parent account linked to this student.',
            ]);
        }

        Auth::guard('parent')->login($guardian);
        $this->sessionService->log($guardian, null, 'login', 'Parent logged in via reference code + DOB');
        $request->session()->regenerate();

        return redirect()->intended(route('parent.dashboard'))
                         ->with('success', 'Welcome! Showing health records for ' . $student->name);
    }

    // ── Doctor Login ─────────────────────────────────────────
    public function doctorLogin(Request $request)
    {
        $request->validate([
            'staff_code'   => 'required|string',
            'session_code' => 'required|string',
        ]);

        $result = $this->sessionService->authenticateDoctor(
            strtoupper(trim($request->staff_code)),
            strtoupper(trim($request->session_code))
        );

        if (! $result['success']) {
            throw ValidationException::withMessages([
                'staff_code' => $result['message'],
            ]);
        }

        $doctor  = $result['doctor'];
        $session = $result['session'];

        Auth::guard('doctor')->login($doctor);
        $request->session()->regenerate();

        session(['doctor_session_id' => $session->id]);

        return redirect()->route('doctor.session.active')
                         ->with('success', 'Welcome, Dr. ' . $doctor->name . '! Session active.');
    }

    // ── Logout ───────────────────────────────────────────────
    public function logout(Request $request)
    {
        $doctor   = Auth::guard('doctor')->user();
        $guardian = Auth::guard('parent')->user();
        $admin    = Auth::guard('web')->user();

        if ($doctor) {
            $doctorSession = session('doctor_session_id')
                ? \App\Models\DoctorSession::find(session('doctor_session_id'))
                : null;
            $this->sessionService->log($doctor, $doctorSession, 'logout', 'Doctor logged out');
            Auth::guard('doctor')->logout();
        } elseif ($guardian) {
            $this->sessionService->log($guardian, null, 'logout', 'Parent logged out');
            Auth::guard('parent')->logout();
        } elseif ($admin) {
            $this->sessionService->log($admin, null, 'logout', 'Admin logged out');
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
