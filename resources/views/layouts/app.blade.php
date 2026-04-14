<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title', 'Dashboard') — CheckMyFitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --dk:#0A1628;--dk2:#0F2238;
  --g:#1D9E75;--lg:#4ADE80;--g2:#0F6E56;
  --r:#EF4444;--or:#F59E0B;--bl:#3B82F6;--pu:#8B5CF6;
  --gr:#64748B;--lgr:#F1F5F9;--bd:#E2EDE9;
  --sidebar:236px;
  --ff:'Fraunces',serif;--fb:'DM Sans',sans-serif;
}
body{font-family:var(--fb);background:#F0FDF9;color:var(--dk);-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}
button,input,select,textarea{font-family:var(--fb);}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-thumb{background:var(--bd);border-radius:4px;}

/* ── Sidebar ──────────────────────────────────────── */
.sidebar{
  width:var(--sidebar);position:fixed;top:0;left:0;bottom:0;
  background:var(--dk);z-index:200;
  display:flex;flex-direction:column;
  transition:transform .28s ease;
}
.sb-logo{padding:22px 18px;border-bottom:1px solid rgba(255,255,255,0.07);}
.sb-brand{font-family:var(--ff);font-size:17px;font-weight:900;color:#fff;}
.sb-brand span{color:var(--lg);}
.sb-role{display:inline-block;margin-top:6px;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.5px;}
.sb-nav{flex:1;padding:12px 10px;overflow-y:auto;}
.nb-label{font-size:9px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,0.22);padding:12px 10px 5px;}
.ni{display:flex;align-items:center;gap:10px;padding:10px 11px;border-radius:10px;font-size:13px;font-weight:500;color:rgba(255,255,255,0.52);cursor:pointer;transition:all .18s;margin-bottom:1px;width:100%;text-align:left;border:none;background:none;}
.ni:hover{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.85);}
.ni.active{font-weight:700;color:#fff;}
.ni-ico{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.sb-foot{padding:10px;border-top:1px solid rgba(255,255,255,0.07);}
.sb-user{display:flex;align-items:center;gap:10px;padding:10px 11px;margin-bottom:4px;}
.sb-av{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;}
.sb-uname{font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.logout-btn{width:100%;display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:10px;font-size:13px;font-weight:500;color:rgba(255,255,255,0.38);cursor:pointer;background:none;border:none;transition:all .18s;}
.logout-btn:hover{background:rgba(239,68,68,0.1);color:#EF4444;}

/* ── Main ─────────────────────────────────────────── */
.main{margin-left:var(--sidebar);min-height:100vh;display:flex;flex-direction:column;}
.topbar{background:#fff;border-bottom:1px solid var(--bd);height:62px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.tb-l{display:flex;align-items:center;gap:12px;}
.hamburger{display:none;background:none;border:none;font-size:20px;cursor:pointer;padding:6px;}
.tb-title{font-family:var(--ff);font-size:19px;font-weight:900;color:var(--dk);}
.page-body{padding:24px 28px;flex:1;}

/* ── Cards / Shared ───────────────────────────────── */
.card{background:#fff;border:1.5px solid var(--bd);border-radius:16px;padding:20px 22px;margin-bottom:18px;}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.card-title{font-size:14px;font-weight:700;color:var(--dk);}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.scard{background:#fff;border:1.5px solid var(--bd);border-radius:16px;padding:18px 20px;}
.sc-l{font-size:11px;color:var(--gr);margin-bottom:5px;font-weight:500;}
.sc-v{font-family:var(--ff);font-size:28px;font-weight:900;color:var(--dk);line-height:1;}
.sc-s{font-size:11px;font-weight:600;margin-top:5px;}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}

/* Badges */
.badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;}
.bg{background:#F0FDF9;color:#065F46;}
.by{background:#FFFBEB;color:#92400E;}
.br{background:#FEF2F2;color:#991B1B;}
.bb{background:#EFF6FF;color:#1E40AF;}
.bp{background:#F5F3FF;color:#5B21B6;}
.bgr{background:var(--lgr);color:var(--gr);}

/* Table */
.tw{overflow-x:auto;border:1.5px solid var(--bd);border-radius:12px;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{padding:10px 15px;background:var(--lgr);color:var(--gr);font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--bd);text-align:left;white-space:nowrap;}
td{padding:11px 15px;border-bottom:1px solid #F8FFFE;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#FAFEFF;}

/* Buttons */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:none;border-radius:10px;padding:10px 18px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;font-family:var(--fb);}
.btn-g{background:var(--g);color:#fff;}       .btn-g:hover{background:var(--g2);}
.btn-b{background:var(--bl);color:#fff;}      .btn-b:hover{background:#1E40AF;}
.btn-p{background:var(--pu);color:#fff;}      .btn-p:hover{background:#5B21B6;}
.btn-r{background:var(--r);color:#fff;}       .btn-r:hover{background:#B91C1C;}
.btn-or{background:var(--or);color:#fff;}
.btn-dk{background:var(--dk);color:#fff;}
.btn-out{background:#fff;border:1.5px solid var(--bd);color:var(--dk);}
.btn-out:hover{border-color:var(--g);color:var(--g);}
.btn-sm{padding:7px 13px;font-size:12px;border-radius:8px;}
.btn-lg{padding:13px 28px;font-size:15px;font-weight:700;border-radius:12px;}
.btn-full{width:100%;}

/* Forms */
.form-group{margin-bottom:18px;}
.form-label{display:block;font-size:12px;font-weight:700;color:var(--dk);margin-bottom:6px;}
.form-label .req{color:var(--r);}
.form-input{width:100%;border:1.5px solid var(--bd);border-radius:10px;padding:11px 13px;font-size:14px;color:var(--dk);outline:none;transition:border-color .2s;background:#fff;}
.form-input:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(29,158,117,0.09);}
.form-input::placeholder{color:#94A3B8;}
select.form-input{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2364748B' d='M5 7L0 2h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:34px;}
textarea.form-input{resize:vertical;min-height:80px;line-height:1.6;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-hint{font-size:11px;color:var(--gr);margin-top:4px;}

/* Alerts */
.alert{border-radius:12px;padding:12px 16px;display:flex;gap:10px;margin-bottom:10px;}
.alert-r{background:#FEF2F2;color:#DC2626;}
.alert-g{background:#F0FDF9;color:#065F46;}
.alert-y{background:#FFFBEB;color:#92400E;}
.alert-b{background:#EFF6FF;color:#1E40AF;}

/* Session expired banner */
@if(session('doctor_session_expiry_warning'))
.session-warning-bar{background:var(--or);color:#fff;padding:10px 20px;text-align:center;font-size:13px;font-weight:600;}
@endif

/* Sidebar overlay */
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:150;}
.sb-overlay.on{display:block;}

/* Responsive */
@media(max-width:900px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.open{transform:translateX(0);}
  .main{margin-left:0;}
  .hamburger{display:block;}
  .stat-grid{grid-template-columns:1fr 1fr;}
  .g2{grid-template-columns:1fr;}
  .page-body{padding:16px;}
  .form-grid{grid-template-columns:1fr;}
}
@media(max-width:480px){
  .stat-grid{grid-template-columns:1fr;}
  .g3{grid-template-columns:1fr;}
}
</style>
@stack('head')
</head>
<body>
<div class="sb-overlay" id="sb-overlay" onclick="closeSB()"></div>

<div class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="sb-brand">CheckMy<span>Fitness</span></div>
    @php $roleLabel = auth()->user()->getRoleNames()->first(); @endphp
    <div class="sb-role" style="background:{{ match($roleLabel) { 'admin'=>'#8B5CF622','doctor'=>'#3B82F622',default=>'#1D9E7522' } }};color:{{ match($roleLabel) { 'admin'=>'#8B5CF6','doctor'=>'#3B82F6',default=>'#1D9E75' } }};">
      {{ ucfirst($roleLabel) }}
    </div>
  </div>

  <nav class="sb-nav">
    @yield('sidebar-nav')
  </nav>

  <div class="sb-foot">
    <div class="sb-user">
      <div class="sb-av" style="background:{{ match($roleLabel) { 'admin'=>'#8B5CF6','doctor'=>'#3B82F6',default=>'#1D9E75' } }};">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div class="sb-uname">{{ auth()->user()->name }}</div>
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
      @if(auth()->user()->isDoctor() && session('doctor_session_id'))
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

<script>
function toggleSB(){ document.getElementById('sidebar').classList.toggle('open'); document.getElementById('sb-overlay').classList.toggle('on'); }
function closeSB(){ document.getElementById('sidebar').classList.remove('open'); document.getElementById('sb-overlay').classList.remove('on'); }

// Doctor session expiry countdown
@if(auth()->user()?->isDoctor() && session('doctor_session_id'))
  @php $ds2 = \App\Models\DoctorSession::find(session('doctor_session_id')); @endphp
  @if($ds2 && !$ds2->isExpired())
  (function(){
    const expiresAt = new Date('{{ $ds2->expires_at->toIso8601String() }}');
    setInterval(function(){
      const diff = Math.round((expiresAt - new Date()) / 1000);
      if(diff <= 0){ location.reload(); return; }
      if(diff < 1800){ // last 30 min — warn
        const el = document.querySelector('.session-timer');
        if(el) el.textContent = Math.floor(diff/60) + 'm ' + (diff%60) + 's';
      }
    }, 1000);
  })();
  @endif
@endif
</script>
@stack('scripts')
</body>
</html>
