<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DoctorSession;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_schools'   => School::where('is_active', true)->count(),
            'total_doctors'   => User::role('doctor')->where('is_active', true)->count(),
            'total_parents'   => User::role('parent')->where('is_active', true)->count(),
            'total_students'  => \App\Models\Student::where('is_active', true)->count(),
            'active_sessions' => DoctorSession::active()->count(),
            'pending_sessions'=> DoctorSession::where('status', 'pending')->count(),
            'total_checkups'  => \App\Models\Checkup::completed()->count(),
            'total_alerts'    => \App\Models\Checkup::completed()
                                     ->whereNotNull('alerts')
                                     ->get()
                                     ->flatMap(fn($c) => $c->alerts ?? [])
                                     ->count(),
        ];

        $activeSessions = DoctorSession::with(['doctor', 'createdByAdmin'])
                                       ->active()
                                       ->latest('last_activity_at')
                                       ->take(5)
                                       ->get();

        $recentLogs = ActivityLog::with(['user', 'doctorSession'])
                                  ->latest()
                                  ->take(20)
                                  ->get();

        $recentSessions = DoctorSession::with(['doctor'])
                                        ->latest()
                                        ->take(10)
                                        ->get();

        $schoolPerformance = School::where('is_active', true)->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'activeSessions', 'recentLogs', 'recentSessions', 'schoolPerformance'));
    }
}
