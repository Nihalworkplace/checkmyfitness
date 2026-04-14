<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Login — CheckMyFitness</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--dk:#0A1628;--g:#1D9E75;--lg:#4ADE80;--r:#EF4444;--bl:#3B82F6;--pu:#8B5CF6;--gr:#64748B;--bd:#E2EDE9;--lgr:#F1F5F9;--ff:'Fraunces',serif;--fb:'DM Sans',sans-serif;}
body{min-height:100vh;background:var(--dk);font-family:var(--fb);display:flex;align-items:center;justify-content:center;padding:24px;position:relative;overflow:hidden;-webkit-font-smoothing:antialiased;}
.bg-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(29,158,117,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(29,158,117,0.06) 1px,transparent 1px);background-size:52px 52px;pointer-events:none;}
.glow{position:absolute;border-radius:50%;pointer-events:none;}
.g1{width:600px;height:600px;right:-180px;top:-180px;background:radial-gradient(circle,rgba(29,158,117,0.14),transparent 65%);}
.g2{width:350px;height:350px;left:-100px;bottom:-100px;background:radial-gradient(circle,rgba(74,222,128,0.08),transparent 65%);}

.card{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:28px;padding:40px 36px;width:100%;max-width:440px;backdrop-filter:blur(24px);position:relative;z-index:2;}
.brand{text-align:center;margin-bottom:28px;}
.logo{font-family:var(--ff);font-size:26px;font-weight:900;color:#fff;}
.logo span{color:var(--lg);}
.tagline{font-size:12px;color:rgba(255,255,255,0.3);margin-top:4px;}

/* Role tabs */
.role-tabs{display:grid;grid-template-columns:repeat(3,1fr);background:rgba(255,255,255,0.06);border-radius:14px;padding:4px;gap:3px;margin-bottom:28px;}
.rtab{padding:11px 6px;border-radius:11px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.4);text-align:center;cursor:pointer;transition:all .2s;border:none;background:none;}
.rtab:hover{color:rgba(255,255,255,.7);}
.rtab[data-role="parent"].active{background:var(--g);color:#fff;}
.rtab[data-role="doctor"].active{background:var(--bl);color:#fff;}
.rtab[data-role="admin"].active{background:var(--pu);color:#fff;}

/* Form panels */
.panel{display:none;}
.panel.active{display:block;}

/* Info box */
.info-box{background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.25);border-radius:12px;padding:12px 14px;margin-bottom:18px;}
.info-box p{font-size:12px;color:rgba(255,255,255,0.7);line-height:1.5;}
.info-box strong{color:#93C5FD;}

/* Form fields */
.fg{margin-bottom:16px;}
.fl{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,0.4);display:block;margin-bottom:6px;}
.fi{width:100%;background:rgba(255,255,255,0.08);border:1.5px solid rgba(255,255,255,0.1);border-radius:12px;padding:13px 16px;color:#fff;font-size:14px;font-family:var(--fb);outline:none;transition:border-color .2s;}
.fi:focus{border-color:rgba(29,158,117,0.6);background:rgba(255,255,255,0.1);}
.fi::placeholder{color:rgba(255,255,255,0.22);}
.fi-hint{font-size:11px;color:rgba(255,255,255,0.25);margin-top:5px;}

/* Login type toggle (parent) */
.login-toggle{display:flex;gap:8px;margin-bottom:18px;}
.lt-btn{flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:600;border:1.5px solid rgba(255,255,255,0.12);background:none;color:rgba(255,255,255,0.45);cursor:pointer;transition:all .2s;}
.lt-btn.active{background:rgba(29,158,117,0.2);border-color:var(--g);color:var(--lg);}

/* Submit btn */
.submit-btn{width:100%;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:700;color:#fff;cursor:pointer;font-family:var(--fb);margin-top:8px;transition:all .2s;}
.submit-btn:active{transform:scale(.98);}
.submit-parent{background:var(--g);}  .submit-parent:hover{background:#0F6E56;}
.submit-doctor{background:var(--bl);} .submit-doctor:hover{background:#1E40AF;}
.submit-admin{background:var(--pu);}  .submit-admin:hover{background:#5B21B6;}

/* Errors */
.err{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#FCA5A5;line-height:1.5;}

/* Demo note */
.demo{margin-top:20px;background:rgba(255,255,255,0.04);border-radius:12px;padding:14px;font-size:12px;color:rgba(255,255,255,0.35);text-align:center;line-height:1.7;}
.demo strong{color:rgba(255,255,255,0.6);}

@media(max-width:480px){.card{padding:28px 18px;}}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="glow g1"></div>
<div class="glow g2"></div>

<div class="card">
  <div class="brand">
    <div class="logo">CheckMy<span>Fitness</span></div>
    <div class="tagline">Health Management Platform · Checkmy.fitness</div>
  </div>

  {{-- Error messages --}}
  @if($errors->any())
    <div class="err">
      @foreach($errors->all() as $err){{ $err }}<br/>@endforeach
    </div>
  @endif

  {{-- Role tabs --}}
  <div class="role-tabs">
    <button class="rtab {{ $role==='parent'?'active':'' }}" data-role="parent" onclick="switchRole('parent',this)">👪 Parent</button>
    <button class="rtab {{ $role==='doctor'?'active':'' }}" data-role="doctor" onclick="switchRole('doctor',this)">🩺 Doctor</button>
    <button class="rtab {{ $role==='admin'?'active':'' }}"  data-role="admin"  onclick="switchRole('admin',this)">⚙️ Admin</button>
  </div>

  {{-- ── PARENT PANEL ── --}}
  <div class="panel {{ $role==='parent'?'active':'' }}" id="panel-parent">
    <div class="login-toggle">
      <button class="lt-btn active" id="lt-email" onclick="switchLoginType('email')">📧 Email & Password</button>
      <button class="lt-btn"        id="lt-code"  onclick="switchLoginType('code')">🔑 Reference Code</button>
    </div>

    {{-- Email login --}}
    <form method="POST" action="{{ route('login.parent') }}" id="form-email">
      @csrf
      <input type="hidden" name="login_type" value="email"/>
      <div class="fg">
        <label class="fl">Email Address</label>
        <input class="fi" type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" required/>
      </div>
      <div class="fg">
        <label class="fl">Password</label>
        <input class="fi" type="password" name="password" placeholder="••••••••" required/>
      </div>
      <button type="submit" class="submit-btn submit-parent">View My Child's Health Report →</button>
    </form>

    {{-- Reference code login --}}
    <form method="POST" action="{{ route('login.parent') }}" id="form-code" style="display:none;">
      @csrf
      <input type="hidden" name="login_type" value="code"/>
      <div class="fg">
        <label class="fl">Student Reference Code</label>
        <input class="fi" type="text" name="reference_code" placeholder="CMF-2024-06B-042" value="{{ old('reference_code') }}" style="text-transform:uppercase;" required/>
        <div class="fi-hint">Found on your child's school communication from CheckMyFitness</div>
      </div>
      <button type="submit" class="submit-btn submit-parent">Access Health Records →</button>
    </form>
  </div>

  {{-- ── DOCTOR PANEL ── --}}
  <div class="panel {{ $role==='doctor'?'active':'' }}" id="panel-doctor">
    <div class="info-box">
      <p><strong>Session-based login.</strong> Admin creates a session before each school visit and shares a unique code with you. You cannot log in without a valid session code.</p>
    </div>
    <form method="POST" action="{{ route('login.doctor') }}">
      @csrf
      <div class="fg">
        <label class="fl">Doctor / Staff ID</label>
        <input class="fi" type="text" name="staff_code" placeholder="CMF-DOC-0021" value="{{ old('staff_code') }}" style="text-transform:uppercase;" required/>
        <div class="fi-hint">Your permanent staff code — provided by CheckMyFitness admin</div>
      </div>
      <div class="fg">
        <label class="fl">Session Access Code</label>
        <input class="fi" type="text" name="session_code" placeholder="SESS-DPS-20260329-XXXX" value="{{ old('session_code') }}" style="text-transform:uppercase;" required/>
        <div class="fi-hint">Generated by admin for today's school visit. Codes expire and cannot be reused.</div>
      </div>
      <button type="submit" class="submit-btn submit-doctor">Enter Checkup Session →</button>
    </form>
  </div>

  {{-- ── ADMIN PANEL ── --}}
  <div class="panel {{ $role==='admin'?'active':'' }}" id="panel-admin">
    <form method="POST" action="{{ route('login.admin') }}">
      @csrf
      <div class="fg">
        <label class="fl">Admin Email</label>
        <input class="fi" type="email" name="email" placeholder="admin@checkmy.fitness" value="{{ old('email') }}" required/>
      </div>
      <div class="fg">
        <label class="fl">Password</label>
        <input class="fi" type="password" name="password" placeholder="••••••••" required/>
        <div class="fi-hint">Contact CheckMyFitness if you have forgotten your admin password</div>
      </div>
      <button type="submit" class="submit-btn submit-admin">Admin Login →</button>
    </form>
  </div>

  <div class="demo">
    <strong>Demo Credentials</strong><br/>
    Admin: admin@checkmy.fitness / Admin@2026<br/>
    Parent: rajesh.shah@example.com / Parent@2026 &nbsp;|&nbsp; OR code: CMF-2024-06B-042<br/>
    Doctor: CMF-DOC-0021 + SESS-DPS-DEMO-2026
  </div>
</div>

<script>
function switchRole(role, el) {
  document.querySelectorAll('.rtab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-' + role).classList.add('active');
  history.replaceState(null, '', '?role=' + role);
}
function switchLoginType(type) {
  const emailForm = document.getElementById('form-email');
  const codeForm  = document.getElementById('form-code');
  const ltEmail   = document.getElementById('lt-email');
  const ltCode    = document.getElementById('lt-code');
  if (type === 'email') {
    emailForm.style.display = 'block'; codeForm.style.display = 'none';
    ltEmail.classList.add('active'); ltCode.classList.remove('active');
  } else {
    emailForm.style.display = 'none'; codeForm.style.display = 'block';
    ltEmail.classList.remove('active'); ltCode.classList.add('active');
  }
}
// Auto-uppercase staff/session code inputs
document.querySelectorAll('[style*="text-transform:uppercase"]').forEach(el => {
  el.addEventListener('input', () => el.value = el.value.toUpperCase());
});
</script>
</body>
</html>
