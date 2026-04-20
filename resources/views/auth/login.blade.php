<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login — CheckMyFitness</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>
<body>

    {{-- Background decorations --}}
    <div class="bg-grid"></div>
    <div class="glow glow--top-right"></div>
    <div class="glow glow--bottom-left"></div>

    <div class="login-card">

        {{-- Brand --}}
        <div class="brand">
            <div class="brand__logo">CheckMy<span>Fitness</span></div>
            <div class="brand__tagline">Health Management Platform · Checkmy.fitness</div>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br />
                @endforeach
            </div>
        @endif

        {{-- Role tabs --}}
        <div class="role-tabs">
            <button class="role-tab {{ $role === 'parent' ? 'active' : '' }}"
                    data-role="parent"
                    onclick="switchRole('parent', this)">
                👪 Parent
            </button>
            <button class="role-tab {{ $role === 'doctor' ? 'active' : '' }}"
                    data-role="doctor"
                    onclick="switchRole('doctor', this)">
                🩺 Doctor
            </button>
            <button class="role-tab {{ $role === 'admin' ? 'active' : '' }}"
                    data-role="admin"
                    onclick="switchRole('admin', this)">
                ⚙️ Admin
            </button>
        </div>
 
        {{-- ── PARENT PANEL ── --}}
        <div class="panel {{ $role === 'parent' ? 'active' : '' }}" id="panel-parent">

            {{-- Login-type toggle --}}
            <div class="login-toggle">
                <button class="login-toggle__btn active" id="lt-email" onclick="switchLoginType('email')">
                    📧 Email &amp; Password
                </button>
                <button class="login-toggle__btn" id="lt-code" onclick="switchLoginType('code')">
                    🔑 Reference Code
                </button>
            </div>

            {{-- Email + password form --}}
            <form method="POST" action="{{ route('login.parent') }}" id="form-email">
                @csrf
                <input type="hidden" name="login_type" value="email" />

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-input"
                           type="email"
                           name="email"
                           placeholder="your@email.com"
                           value="{{ old('email') }}"
                           required />
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input class="form-input"
                           type="password"
                           name="password"
                           placeholder="••••••••"
                           required />
                </div>

                <button type="submit" class="submit-btn submit-btn--parent">
                    View My Child's Health Report →
                </button>
            </form>

            {{-- Reference code + date-of-birth form --}}
            <form method="POST" action="{{ route('login.parent') }}" id="form-code" style="display:none;">
                @csrf
                <input type="hidden" name="login_type" value="code" />

                <div class="form-group">
                    <label class="form-label">Student Reference Code</label>
                    <input class="form-input form-input--uppercase"
                           type="text"
                           name="reference_code"
                           placeholder="CMF-2024-06B-042"
                           value="{{ old('reference_code') }}"
                           required />
                    <div class="form-hint">Found on your child's school communication from CheckMyFitness</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Child's Date of Birth</label>
                    <input class="form-input"
                           type="date"
                           name="date_of_birth"
                           value="{{ old('date_of_birth') }}"
                           required />
                    <div class="form-hint">Enter your child's date of birth to verify identity</div>
                </div>

                <button type="submit" class="submit-btn submit-btn--parent">
                    Access Health Records →
                </button>
            </form>

        </div>{{-- /panel-parent --}}

        {{-- ── DOCTOR PANEL ── --}}
        <div class="panel {{ $role === 'doctor' ? 'active' : '' }}" id="panel-doctor">

            <div class="info-box">
                <p>
                    <strong>Session-based login.</strong>
                    Admin creates a session before each school visit and shares a unique code with you.
                    You cannot log in without a valid session code.
                </p>
            </div>

            <form method="POST" action="{{ route('login.doctor') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Doctor / Staff ID</label>
                    <input class="form-input form-input--uppercase"
                           type="text"
                           name="staff_code"
                           placeholder="CMF-DOC-0021"
                           value="{{ old('staff_code') }}"
                           required />
                    <div class="form-hint">Your permanent staff code — provided by CheckMyFitness admin</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Session Access Code</label>
                    <input class="form-input form-input--uppercase"
                           type="text"
                           name="session_code"
                           placeholder="SESS-DPS-20260329-XXXX"
                           value="{{ old('session_code') }}"
                           required />
                    <div class="form-hint">Generated by admin for today's school visit. Codes expire and cannot be reused.</div>
                </div>

                <button type="submit" class="submit-btn submit-btn--doctor">
                    Enter Checkup Session →
                </button>
            </form>

        </div>{{-- /panel-doctor --}}

        {{-- ── ADMIN PANEL ── --}}
        <div class="panel {{ $role === 'admin' ? 'active' : '' }}" id="panel-admin">

            <form method="POST" action="{{ route('login.admin') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Admin Email</label>
                    <input class="form-input"
                           type="email"
                           name="email"
                           placeholder="admin@checkmy.fitness"
                           value="{{ old('email') }}"
                           required />
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input class="form-input"
                           type="password"
                           name="password"
                           placeholder="••••••••"
                           required />
                    <div class="form-hint">Contact CheckMyFitness if you have forgotten your admin password</div>
                </div>

                <button type="submit" class="submit-btn submit-btn--admin">
                    Admin Login →
                </button>
            </form>

        </div>{{-- /panel-admin --}}


    </div>{{-- /login-card --}}

    <script src="{{ asset('js/login.js') }}"></script>

</body>
</html>
