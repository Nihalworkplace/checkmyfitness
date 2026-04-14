<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CommunityController as AdminCommunityController;
use App\Http\Controllers\Doctor\CheckupController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboard;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes — no auth required
|--------------------------------------------------------------------------
*/

// Root → login (no landing page)
Route::get('/', fn() => redirect()->route('login'));

// Login page
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');

// Login form submissions
Route::post('/login/admin',  [LoginController::class, 'adminLogin'])->name('login.admin');
Route::post('/login/parent', [LoginController::class, 'parentLogin'])->name('login.parent');
Route::post('/login/doctor', [LoginController::class, 'doctorLogin'])->name('login.doctor');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
     ->name('admin.')
     ->middleware(['auth', 'role:admin', 'activity.log'])
     ->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Doctor management
    Route::get('/doctors',               [AdminUserController::class, 'doctors'])->name('doctors');
    Route::get('/doctors/create',        [AdminUserController::class, 'createDoctor'])->name('doctors.create');
    Route::post('/doctors',              [AdminUserController::class, 'storeDoctor'])->name('doctors.store');
    Route::patch('/doctors/{doctor}/toggle', [AdminUserController::class, 'toggleDoctorStatus'])->name('doctors.toggle');

    // Parent management
    Route::get('/parents',               [AdminUserController::class, 'parents'])->name('parents');
    Route::get('/parents/create',        [AdminUserController::class, 'createParent'])->name('parents.create');
    Route::post('/parents',              [AdminUserController::class, 'storeParent'])->name('parents.store');

    // Student management
    Route::get('/students',              [AdminUserController::class, 'students'])->name('students');
    Route::get('/students/create',       [AdminUserController::class, 'createStudent'])->name('students.create');
    Route::post('/students',             [AdminUserController::class, 'storeStudent'])->name('students.store');

    // Session management
    Route::get('/sessions',              [AdminSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/create',       [AdminSessionController::class, 'create'])->name('sessions.create');
    Route::post('/sessions',             [AdminSessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{session}',    [AdminSessionController::class, 'show'])->name('sessions.show');
    Route::post('/sessions/{session}/revoke',  [AdminSessionController::class, 'revoke'])->name('sessions.revoke');
    Route::post('/sessions/{session}/reopen',  [AdminSessionController::class, 'reopen'])->name('sessions.reopen');

    // Activity logs
    Route::get('/logs', [AdminSessionController::class, 'logs'])->name('logs');

    // Health alerts
    Route::get('/alerts', [AdminSessionController::class, 'alerts'])->name('alerts');

    // Schools CRUD
    Route::get('/schools',                     [AdminSchoolController::class, 'index'])->name('schools.index');
    Route::get('/schools/create',              [AdminSchoolController::class, 'create'])->name('schools.create');
    Route::post('/schools',                    [AdminSchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}',            [AdminSchoolController::class, 'show'])->name('schools.show');
    Route::get('/schools/{school}/edit',       [AdminSchoolController::class, 'edit'])->name('schools.edit');
    Route::put('/schools/{school}',            [AdminSchoolController::class, 'update'])->name('schools.update');
    Route::patch('/schools/{school}/toggle',   [AdminSchoolController::class, 'toggle'])->name('schools.toggle');

    // Community
    Route::get('/community',                         [AdminCommunityController::class, 'index'])->name('community.index');
    Route::post('/community',                        [AdminCommunityController::class, 'store'])->name('community.store');
    Route::post('/community/{post}/boost',           [AdminCommunityController::class, 'boost'])->name('community.boost');
    Route::delete('/community/{post}',               [AdminCommunityController::class, 'destroy'])->name('community.destroy');
});

/*
|--------------------------------------------------------------------------
| Doctor Routes — requires valid doctor session
|--------------------------------------------------------------------------
*/
Route::prefix('doctor')
     ->name('doctor.')
     ->middleware(['auth', 'role:doctor', 'doctor.session', 'activity.log'])
     ->group(function () {

    Route::get('/session',          [CheckupController::class, 'activeSession'])->name('session.active');
    Route::get('/checkup/{student}',[CheckupController::class, 'showForm'])->name('checkup.form');
    Route::post('/checkup/{student}',[CheckupController::class, 'saveCheckup'])->name('checkup.save');
    Route::get('/completed',        [CheckupController::class, 'completed'])->name('completed');
    Route::get('/summary',          [CheckupController::class, 'summary'])->name('summary');
});

/*
|--------------------------------------------------------------------------
| Parent Routes
|--------------------------------------------------------------------------
*/
Route::prefix('parent')
     ->name('parent.')
     ->middleware(['auth', 'role:parent', 'activity.log'])
     ->group(function () {

    Route::get('/dashboard',                  [ParentDashboard::class, 'index'])->name('dashboard');
    Route::get('/report/{student}',           [ParentDashboard::class, 'report'])->name('report');
    Route::get('/timeline/{student}',         [ParentDashboard::class, 'timeline'])->name('timeline');
    Route::get('/rewards/{student}',          [ParentDashboard::class, 'rewards'])->name('rewards');
});
