<?php

namespace App\Http\Middleware;

use App\Models\DoctorSession;
use App\Services\DoctorSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DoctorSessionMiddleware
{
    public function __construct(private DoctorSessionService $sessionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Auth::guard('doctor')->user();

        if (! $doctor) {
            return redirect()->route('login')->withErrors(['error' => 'Access denied.']);
        }

        $sessionId = session('doctor_session_id');

        if (! $sessionId) {
            return $this->forceLogout($request, 'No active session found. Please log in again.');
        }

        $doctorSession = DoctorSession::find($sessionId);

        if (! $doctorSession) {
            return $this->forceLogout($request, 'Session not found. Please log in again.');
        }

        if ($doctorSession->doctor_id !== $doctor->id) {
            return $this->forceLogout($request, 'Session mismatch detected.');
        }

        if ($doctorSession->isExpired()) {
            $doctorSession->update(['status' => 'expired']);
            $this->sessionService->log($doctor, $doctorSession, 'session_expired', 'Session expired — doctor forced logout');
            return $this->forceLogout($request, 'Your session has expired. Please contact admin for a new session code.');
        }

        if ($doctorSession->isRevoked()) {
            $this->sessionService->log($doctor, $doctorSession, 'session_revoked', 'Session revoked — doctor forced logout');
            return $this->forceLogout($request, 'Your session has been revoked by admin. Please contact your administrator.');
        }

        if (! in_array($doctorSession->status, ['active', 'pending'])) {
            return $this->forceLogout($request, 'Session is no longer active.');
        }

        $doctorSession->touchActivity();

        $request->attributes->set('doctor_session', $doctorSession);
        app()->instance('doctor_session', $doctorSession);

        return $next($request);
    }

    private function forceLogout(Request $request, string $message): Response
    {
        Auth::guard('doctor')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login', ['role' => 'doctor'])
                         ->withErrors(['session' => $message]);
    }
}
