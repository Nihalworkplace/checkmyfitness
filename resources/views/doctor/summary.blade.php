@extends('layouts.app')
@section('title','Session Summary')
@section('page-title','Session Summary')
@section('sidebar-nav')
<a href="{{ route('doctor.session.active') }}" class="ni"><div class="ni-ico" style="background:rgba(59,130,246,0.25);">🩺</div> Checkup Session</a>
<a href="{{ route('doctor.completed') }}" class="ni"><div class="ni-ico" style="background:rgba(29,158,117,0.2);">✅</div> Completed Today</a>
<a href="{{ route('doctor.summary') }}" class="ni active" style="background:rgba(245,158,11,0.15);color:#fff;"><div class="ni-ico" style="background:rgba(245,158,11,0.3);">📊</div> Session Summary</a>
@endsection

@section('content')
{{-- Session info banner --}}
<div style="background:var(--dk);border-radius:16px;padding:18px 22px;margin-bottom:18px;">
  <div style="font-size:10px;color:rgba(255,255,255,0.35);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px;">Session Report</div>
  <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:#fff;">{{ $doctorSession->school_name }}</div>
  <div style="font-size:12px;color:rgba(255,255,255,0.45);">{{ ($doctorSession->starts_at ?? $doctorSession->created_at)->inDisplayTz()->format('d M Y') }} · Code: {{ $doctorSession->session_code }} · Dr. {{ auth()->user()->name }}</div>
</div>

<div class="stat-grid" style="margin-bottom:18px;">
  <div class="scard"><div class="sc-l">Total Students</div><div class="sc-v">{{ $checkups->count() }}</div></div>
  <div class="scard"><div class="sc-l">Completed</div><div class="sc-v" style="color:var(--g);">{{ $stats['completed'] }}</div></div>
  <div class="scard"><div class="sc-l">Drafts</div><div class="sc-v" style="color:var(--or);">{{ $stats['drafts'] }}</div></div>
  <div class="scard"><div class="sc-l">Avg Score</div><div class="sc-v" style="color:var(--bl);">{{ $stats['avg_score'] ? round($stats['avg_score']) : '—' }}</div></div>
  <div class="scard"><div class="sc-l">Total Alerts</div><div class="sc-v" style="color:var(--r);">{{ $stats['total_alerts'] }}</div></div>
  <div class="scard"><div class="sc-l">Critical Alerts</div><div class="sc-v" style="color:var(--r);">{{ $stats['critical_alerts'] }}</div></div>
  <div class="scard"><div class="sc-l">Session Duration</div><div class="sc-v" style="font-size:18px;">{{ $doctorSession->duration_hours }}h</div></div>
  <div class="scard"><div class="sc-l">Activities Logged</div><div class="sc-v">{{ $logs->count() }}</div></div>
</div>

<div class="g2">
  {{-- Parameter averages --}}
  <div class="card">
    <div class="card-header"><div class="card-title">📊 Parameter Averages (Completed)</div></div>
    @php
      $completed = $checkups->where('status','completed');
      $numParams = [
        'haemoglobin_gdl' => ['Haemoglobin', 18, ' g/dL'],
        'vitamin_d_ngml'  => ['Vitamin D', 80, ' ng/mL'],
        'dental_score'    => ['Dental Score', 10, '/10'],
        'mental_score'    => ['Mental Score', 10, '/10'],
        'heart_rate_bpm'  => ['Heart Rate', 140, 'bpm'],
        'spo2_percent'    => ['SpO2', 100, '%'],
        'grip_strength_score' => ['Grip Strength', 10, '/10'],
      ];
    @endphp
    @foreach($numParams as $field => [$label, $max, $unit])
      @php
        $vals = $completed->where($field, '>', 0)->pluck($field)->filter();
        $avg  = $vals->count() ? round($vals->avg(), 1) : null;
        $pct  = $avg ? min(round(($avg/$max)*100), 100) : 0;
        $c    = $pct>=72 ? 'var(--g)' : ($pct>=50 ? 'var(--or)' : 'var(--r)');
      @endphp
      @if($avg)
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--lgr);">
          <div style="font-size:12px;color:var(--gr);width:120px;flex-shrink:0;">{{ $label }}</div>
          <div style="flex:1;height:7px;background:var(--lgr);border-radius:4px;overflow:hidden;">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $c }};border-radius:4px;"></div>
          </div>
          <div style="font-size:12px;font-weight:700;color:{{ $c }};width:60px;text-align:right;">{{ $avg }}{{ $unit }}</div>
        </div>
      @endif
    @endforeach
  </div>

  <div style="display:flex;flex-direction:column;gap:14px;">
    {{-- Most common issues --}}
    <div class="card">
      <div class="card-header"><div class="card-title">⚠️ Most Common Issues</div></div>
      @php
        $allAlerts  = $completed->flatMap(fn($c) => $c->alerts ?? []);
        $grouped    = $allAlerts->groupBy(fn($a) => preg_replace('/\(.*?\)/', '', $a))->sortByDesc->count();
      @endphp
      @forelse($grouped->take(6) as $alert => $instances)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--lgr);font-size:13px;">
          <span style="color:var(--dk);">{{ trim($alert) }}</span>
          <span class="badge {{ $instances->count()>=3?'br':'by' }}">{{ $instances->count() }} student{{ $instances->count()>1?'s':'' }}</span>
        </div>
      @empty
        <div style="text-align:center;padding:16px;color:var(--g);font-size:13px;">✅ No common health issues detected.</div>
      @endforelse
    </div>

    {{-- Session activity log --}}
    <div class="card">
      <div class="card-header"><div class="card-title">📋 My Activity This Session</div></div>
      @foreach($logs->take(10) as $log)
        <div style="display:flex;gap:8px;padding:7px 0;border-bottom:1px solid var(--lgr);font-size:12px;">
          <div style="flex:1;">{{ $log->action_label }}<br/><span style="color:var(--gr);">{{ $log->description }}</span></div>
          <div style="color:var(--gr);flex-shrink:0;white-space:nowrap;">{{ $log->created_at->inDisplayTz()->format('H:i') }}</div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
