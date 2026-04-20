<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\DoctorSession;
use App\Models\Guardian;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $adminId = Auth::id();

        $stats = [
            'total_schools'    => School::where('admin_id', $adminId)->where('is_active', true)->count(),
            'total_doctors'    => Doctor::where('admin_id', $adminId)->where('is_active', true)->count(),
            'total_parents'    => Guardian::where('admin_id', $adminId)->where('is_active', true)->count(),
            'total_students'   => \App\Models\Student::where('admin_id', $adminId)->where('is_active', true)->count(),
            'active_sessions'  => DoctorSession::where('created_by', $adminId)->active()->count(),
            'pending_sessions' => DoctorSession::where('created_by', $adminId)->where('status', 'pending')->count(),
            'total_checkups'   => \App\Models\Checkup::whereHas('doctor', fn($q) => $q->where('admin_id', $adminId))->completed()->count(),
            'total_alerts'     => \App\Models\Checkup::whereHas('doctor', fn($q) => $q->where('admin_id', $adminId))
                                      ->completed()
                                      ->whereNotNull('alerts')
                                      ->get()
                                      ->flatMap(fn($c) => $c->alerts ?? [])
                                      ->count(),
        ];

        $activeSessions = DoctorSession::with(['doctor', 'createdByAdmin'])
                                       ->where('created_by', $adminId)
                                       ->active()
                                       ->latest('last_activity_at')
                                       ->take(5)
                                       ->get();

        $recentLogs = ActivityLog::with(['actor', 'doctorSession'])
                                  ->latest()
                                  ->take(20)
                                  ->get();

        $recentSessions = DoctorSession::with(['doctor'])
                                       ->where('created_by', $adminId)
                                       ->latest()
                                       ->take(10)
                                       ->get();

        $schoolPerformance = School::where('admin_id', $adminId)->where('is_active', true)->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'activeSessions', 'recentLogs', 'recentSessions', 'schoolPerformance'));
    }
}
