<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\Guardian;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogMiddleware
{
    private array $skipActions = ['heartbeat', 'notification', '_debugbar'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $actor = $this->getCurrentActor();

        if ($actor && $this->shouldLog($request)) {
            $this->logActivity($request, $actor);
        }

        return $response;
    }

    private function getCurrentActor()
    {
        return Auth::guard('web')->user()
            ?? Auth::guard('doctor')->user()
            ?? Auth::guard('parent')->user();
    }

    private function getActorRole($actor): string
    {
        return match (true) {
            $actor instanceof Doctor   => 'doctor',
            $actor instanceof Guardian => 'parent',
            default                    => 'admin',
        };
    }

    private function shouldLog(Request $request): bool
    {
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

    private function logActivity(Request $request, $actor): void
    {
        $doctorSession = session('doctor_session_id')
            ? \App\Models\DoctorSession::find(session('doctor_session_id'))
            : null;

        ActivityLog::create([
            'actor_type'        => get_class($actor),
            'actor_id'          => $actor->getAuthIdentifier(),
            'doctor_session_id' => $doctorSession?->id,
            'role'              => $this->getActorRole($actor),
            'action'            => $this->guessAction($request),
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
            'POST'          => str_contains($path, 'checkup') ? 'create_checkup' : 'create_record',
            'PUT', 'PATCH'  => str_contains($path, 'checkup') ? 'update_checkup' : 'update_record',
            'DELETE'        => str_contains($path, 'checkup') ? 'delete_checkup' : 'delete_record',
            default         => strtolower($method) . '_action',
        };
    }
}
