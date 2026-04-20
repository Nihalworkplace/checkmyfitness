<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Dashboard') — CheckMyFitness</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    @stack('head')
</head>
<body>

<div class="sb-overlay" id="sb-overlay" onclick="closeSB()"></div>

<div class="sidebar" id="sidebar">
    @php
        $currentUser = \Illuminate\Support\Facades\Auth::guard('web')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('doctor')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('parent')->user();
        $currentRole = \Illuminate\Support\Facades\Auth::guard('doctor')->check() ? 'doctor'
            : (\Illuminate\Support\Facades\Auth::guard('parent')->check() ? 'parent' : 'admin');
        $roleColors  = ['admin' => ['bg' => '#8B5CF622', 'fg' => '#8B5CF6', 'av' => '#8B5CF6'],
                        'doctor'=> ['bg' => '#3B82F622', 'fg' => '#3B82F6', 'av' => '#3B82F6'],
                        'parent'=> ['bg' => '#1D9E7522', 'fg' => '#1D9E75', 'av' => '#1D9E75']];
        $rc = $roleColors[$currentRole] ?? $roleColors['admin'];
    @endphp
    <div class="sb-logo">
        <div class="sb-brand">CheckMy<span>Fitness</span></div>
        <div class="sb-role" style="background:{{ $rc['bg'] }};color:{{ $rc['fg'] }};">
            {{ ucfirst($currentRole) }}
        </div>
    </div>

    <nav class="sb-nav">
        @yield('sidebar-nav')
    </nav>

    <div class="sb-foot">
        <div class="sb-user">
            <div class="sb-av" style="background:{{ $rc['av'] }};">
                {{ strtoupper(substr($currentUser?->name ?? '?', 0, 1)) }}
            </div>
            <div class="sb-uname">{{ $currentUser?->name ?? '' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">🚪 Sign Out</button>
        </form>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div class="tb-l">
            <button class="hamburger" onclick="toggleSB()">☰</button>
            <div class="tb-title">@yield('page-title', 'Dashboard')</div>
        </div>
        <div>
            @if($currentRole === 'doctor' && session('doctor_session_id'))
                @php $ds = \App\Models\DoctorSession::find(session('doctor_session_id')); @endphp
                @if($ds)
                    <span style="font-size:12px;background:#EFF6FF;color:#1E40AF;padding:5px 12px;border-radius:20px;font-weight:700;">
                        🟢 {{ $ds->school_name }} · Exp: {{ $ds->expires_at->inDisplayTz()->format('H:i') }}
                    </span>
                @endif
            @endif
        </div>
    </div>

    <div class="page-body">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-g" style="margin-bottom:18px;">✅ {{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->any())
            <div class="alert alert-r" style="margin-bottom:18px;">
                ❌ {{ session('error') }}
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>

{{-- Doctor session expiry countdown (uses Blade data — must stay in template) --}}
@if($currentRole === 'doctor' && session('doctor_session_id'))
    @php $ds2 = \App\Models\DoctorSession::find(session('doctor_session_id')); @endphp
    @if($ds2 && !$ds2->isExpired())
        <script>
        (function () {
            var expiresAt = new Date('{{ $ds2->expires_at->toIso8601String() }}');
            setInterval(function () {
                var diff = Math.round((expiresAt - new Date()) / 1000);
                if (diff <= 0) { location.reload(); return; }
                if (diff < 1800) { // last 30 min — show timer
                    var el = document.querySelector('.session-timer');
                    if (el) el.textContent = Math.floor(diff / 60) + 'm ' + (diff % 60) + 's';
                }
            }, 1000);
        })();
        </script>
    @endif
@endif

@stack('scripts')
</body>
</html>
