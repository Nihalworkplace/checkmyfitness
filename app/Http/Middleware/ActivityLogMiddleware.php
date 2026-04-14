<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Automatically logs page views and significant actions for all authenticated users.
 */
class ActivityLogMiddleware
{
    // Routes to skip (avoid logging for every AJAX/minor request)
    private array $skipActions = [
        'heartbeat', 'notification', '_debugbar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $this->shouldLog($request)) {
            $this->logActivity($request);
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        // Skip GET requests to minor endpoints, only log writes and key views
        if ($request->isMethod('GET') && ! $this->isSignificantView($request)) {
            return false;
        }

        foreach ($this->skipActions as $skip) {
            if (str_contains($request->path(), $skip)) {
                return false;
            }
        }

        return true;
    }

    private function isSignificantView(Request $request): bool
    {
        $significantPaths = ['dashboard', 'students', 'checkup', 'session', 'logs', 'alerts'];
        foreach ($significantPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }
        return false;
    }

    private function logActivity(Request $request): void
    {
        $user = $request->user();
        $doctorSession = session('doctor_session_id')
            ? \App\Models\DoctorSession::find(session('doctor_session_id'))
            : null;

        $action = $this->guessAction($request);

        ActivityLog::create([
            'user_id'           => $user->id,
            'doctor_session_id' => $doctorSession?->id,
            'role'              => $user->getRoleNames()->first() ?? 'unknown',
            'action'            => $action,
            'description'       => strtoupper($request->method()) . ' ' . $request->path(),
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'url'               => $request->fullUrl(),
            'method'            => $request->method(),
        ]);
    }

    private function guessAction(Request $request): string
    {
        $method = $request->method();
        $path   = $request->path();

        if ($method === 'GET') {
            if (str_contains($path, 'dashboard')) return 'view_dashboard';
            if (str_contains($path, 'student'))   return 'view_student';
            if (str_contains($path, 'checkup'))   return 'view_checkup';
            if (str_contains($path, 'session'))   return 'view_session';
            if (str_contains($path, 'log'))       return 'view_logs';
            return 'page_view';
        }

        return match ($method) {
            'POST'   => str_contains($path, 'checkup') ? 'create_checkup' : 'create_record',
            'PUT',
            'PATCH'  => str_contains($path, 'checkup') ? 'update_checkup' : 'update_record',
            'DELETE' => str_contains($path, 'checkup') ? 'delete_checkup' : 'delete_record',
            default  => strtolower($method) . '_action',
        };
    }
}
