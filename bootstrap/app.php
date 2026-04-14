<?php

use App\Http\Middleware\ActivityLogMiddleware;
use App\Http\Middleware\DoctorSessionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'role'            => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'      => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'doctor.session'  => DoctorSessionMiddleware::class,
            'activity.log'    => ActivityLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorised.'], 403);
            }
            return redirect()->route('login')->withErrors(['error' => 'You do not have access to that page.']);
        });
    })->create();
