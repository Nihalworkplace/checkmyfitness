<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class DoctorSessionService
{
    /**
     * Generate a new session for a doctor.
     * Called by Admin.
     */
    public function createSession(array $data, User $admin): DoctorSession
    {
        $expiryHours = (int) config('cmf.doctor_session_expiry_hours', 12);
        $startsAt    = $data['starts_at'] ?? now();
        $session = DoctorSession::create([
            'doctor_id'        => $data['doctor_id'],
            'created_by'       => $admin->id,
            'session_code'     => $this->generateUniqueCode($data['school_name']),
            'school_name'      => $data['school_name'],
            'school_city'      => $data['school_city'] ?? null,
            'classes_assigned' => $data['classes_assigned'] ?? [],
            'starts_at'        => $startsAt,
            'expires_at'       => $startsAt->copy()->addHours($expiryHours),
            'status'           => 'pending',
            'admin_notes'      => $data['admin_notes'] ?? null,
        ]);

        $this->log($admin, null, 'create_session', 'Created doctor session for ' . $session->doctor->name, [
            'session_id'   => $session->id,
            'session_code' => $session->session_code,
            'school'       => $session->school_name,
        ]);

        return $session;
    }

    /**
     * Reopen a previous session by creating a new linked session.
     */
    public function reopenSession(DoctorSession $originalSession, array $data, User $admin): DoctorSession
    {
        $expiryHours = (int) config('cmf.doctor_session_expiry_hours', 12);
        $startsAt    = $data['starts_at'] ?? now();

        $newSession = DoctorSession::create([
            'doctor_id'         => $originalSession->doctor_id,
            'created_by'        => $admin->id,
            'parent_session_id' => $originalSession->id,
            'session_code'      => $this->generateUniqueCode($originalSession->school_name),
            'school_name'       => $originalSession->school_name,
            'school_city'       => $originalSession->school_city,
            'classes_assigned'  => $data['classes_assigned'] ?? $originalSession->classes_assigned,
            'starts_at'         => $startsAt,
            'expires_at'        => $startsAt->copy()->addHours($expiryHours),
            'status'            => 'pending',
            'is_reopened'       => true,
            'admin_notes'       => $data['admin_notes'] ?? 'Reopened from session #' . $originalSession->id,
        ]);

        $this->log($admin, null, 'reopen_session', 'Reopened session — new code generated', [
            'original_session_id' => $originalSession->id,
            'new_session_id'      => $newSession->id,
            'new_code'            => $newSession->session_code,
        ]);

        return $newSession;
    }

    /**
     * Authenticate a doctor using staff_code + session_code.
     */
    public function authenticateDoctor(string $staffCode, string $sessionCode): array
    {
        $doctor = Doctor::where('staff_code', $staffCode)
                        ->where('is_active', true)
                        ->first();

        if (! $doctor) {
            return ['success' => false, 'message' => 'Invalid Staff Code. Please check with your administrator.'];
        }

        $session = DoctorSession::where('session_code', $sessionCode)
                                ->where('doctor_id', $doctor->id)
                                ->whereIn('status', ['pending', 'active'])
                                ->first();

        if (! $session) {
            $this->log($doctor, null, 'login_failed', 'Invalid session code attempt', ['staff_code' => $staffCode]);
            return ['success' => false, 'message' => 'Invalid or expired session code. Contact admin for a new code.'];
        }

        if (! $session->isStarted()) {
            $startsLocal = $session->starts_at->copy()->setTimezone(config('app.display_timezone'));
            return ['success' => false, 'message' => 'Session not started yet. Valid from ' . $startsLocal->format('d M Y, h:i A') . '.'];
        }

        if ($session->isExpired()) {
            $session->update(['status' => 'expired']);
            return ['success' => false, 'message' => 'Session code has expired. Ask admin to generate a new one.'];
        }

        if ($session->isRevoked()) {
            return ['success' => false, 'message' => 'This session has been revoked by admin.'];
        }

        DoctorSession::where('doctor_id', $doctor->id)
                     ->where('id', '!=', $session->id)
                     ->where('status', 'active')
                     ->update(['status' => 'completed']);

        $session->markActivated();

        $this->log($doctor, $session, 'login', 'Doctor logged in via session code');

        return ['success' => true, 'doctor' => $doctor, 'session' => $session];
    }

    /**
     * Revoke a session (admin action).
     */
    public function revokeSession(DoctorSession $session, User $admin): void
    {
        $session->revoke();
        $this->log($admin, $session, 'revoke_session', 'Admin revoked doctor session');
    }

    /**
     * Auto-expire sessions that have passed their expiry time.
     */
    public function expireOldSessions(): int
    {
        return DoctorSession::expired()
            ->update(['status' => 'expired']);
    }

    private function generateUniqueCode(string $schoolName): string
    {
        $schoolAbbr = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $schoolName), 0, 4));
        $date       = now()->format('Ymd');
        do {
            $random = strtoupper(Str::random(4));
            $code   = "SESS-{$schoolAbbr}-{$date}-{$random}";
        } while (DoctorSession::where('session_code', $code)->exists());

        return $code;
    }

    /**
     * Log an activity. Actor can be User (admin), Doctor, or Guardian.
     */
    public function log(Authenticatable $actor, ?DoctorSession $session, string $action, string $desc, array $extra = []): void
    {
        $role = match (true) {
            $actor instanceof Doctor   => 'doctor',
            $actor instanceof Guardian => 'parent',
            default                    => 'admin',
        };

        ActivityLog::create([
            'actor_type'        => get_class($actor),
            'actor_id'          => $actor->getAuthIdentifier(),
            'doctor_session_id' => $session?->id,
            'role'              => $role,
            'action'            => $action,
            'description'       => $desc,
            'new_values'        => $extra ?: null,
            'ip_address'        => request()->ip(),
            'user_agent'        => request()->userAgent(),
            'url'               => request()->fullUrl(),
            'method'            => request()->method(),
        ]);
    }
}
