@extends('layouts.app')
@section('title','Session Detail')
@section('page-title','Session Detail')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
{{-- Session header --}}
<div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
  <div>
    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px;">Doctor Session</div>
    <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:900;color:#fff;margin-bottom:4px;">{{ $session->session_code }}</div>
    <div style="font-size:13px;color:rgba(255,255,255,0.5);">
      Dr. {{ $session->doctor->name }} ({{ $session->doctor->staff_code }}) ·
      {{ $session->school_name }}, {{ $session->school_city }} ·
      {{ $session->visit_date->format('d M Y') }}
    </div>
    @if($session->is_reopened)
      <span class="badge by" style="margin-top:8px;">Reopened from Session #{{ $session->parent_session_id }}</span>
    @endif
    @if($session->parentSession)
      <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-top:6px;">
        Linked to: <a href="{{ route('admin.sessions.show', $session->parentSession) }}" style="color:var(--lg);">{{ $session->parentSession->session_code }}</a>
      </div>
    @endif
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start;">
    @php $badge = ['active'=>'bb','pending'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$session->status_badge] ?? 'bgr'; @endphp
    <span class="badge {{ $badge }}" style="font-size:12px;padding:6px 14px;">{{ ucfirst($session->status_badge) }}</span>

    @if(in_array($session->status, ['active','pending']))
      <form method="POST" action="{{ route('admin.sessions.revoke', $session) }}" onsubmit="return confirm('Revoke this session? Doctor will be logged out immediately.')">
        @csrf
        <button type="submit" class="btn btn-r">🚫 Revoke Session</button>
      </form>
    @endif

    @if(in_array($session->status, ['expired','revoked','completed']))
      <button onclick="document.getElementById('reopen-box').classList.toggle('hidden')" class="btn btn-or">🔄 Reopen Session</button>
    @endif
  </div>
</div>

{{-- Reopen form (hidden by default) --}}
<div id="reopen-box" class="hidden card" style="margin-bottom:18px;background:#FFFBEB;">
  <div class="card-header"><div class="card-title">🔄 Reopen Session — New Code Will Be Generated</div></div>
  <div class="alert alert-y" style="margin-bottom:16px;">
    <span>⚠️</span>
    <span style="font-size:13px;">A <strong>brand new session code</strong> will be generated and linked to this session. The old code <strong>cannot be reused</strong>. Share the new code with the doctor.</span>
  </div>
  <form method="POST" action="{{ route('admin.sessions.reopen', $session) }}">
    @csrf
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">New Visit Date <span class="req">*</span></label>
        <input type="date" name="visit_date" class="form-input" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Reason / Notes</label>
        <input type="text" name="admin_notes" class="form-input" placeholder="Reason for reopening…"/>
      </div>
    </div>
    <button type="submit" class="btn btn-or btn-lg">Generate New Session Code →</button>
  </form>
</div>

{{-- Stats row --}}
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px;">
  <div class="scard"><div class="sc-l">Checkups Done</div><div class="sc-v" style="color:var(--g);">{{ $session->checkups->where('status','completed')->count() }}</div></div>
  <div class="scard"><div class="sc-l">Drafts</div><div class="sc-v" style="color:var(--or);">{{ $session->checkups->where('status','draft')->count() }}</div></div>
  <div class="scard"><div class="sc-l">Total Alerts</div><div class="sc-v" style="color:var(--r);">{{ $session->checkups->flatMap(fn($c)=>$c->alerts??[])->count() }}</div></div>
  <div class="scard"><div class="sc-l">Session Duration</div><div class="sc-v" style="font-size:20px;">{{ $session->duration_hours }}h</div></div>
</div>

<div class="g2">
  {{-- Session info --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Session Details</div></div>
    @foreach([
      ['Created By', $session->createdByAdmin->name . ' (Admin)'],
      ['Created At', $session->created_at->inDisplayTz()->format('d M Y H:i')],
      ['Activated At', $session->activated_at?->inDisplayTz()->format('d M Y H:i') ?? 'Not yet activated'],
      ['Last Activity', $session->last_activity_at?->inDisplayTz()->format('d M Y H:i') ?? '—'],
      ['Expires At', $session->expires_at->inDisplayTz()->format('d M Y H:i')],
      ['Classes Assigned', implode(', ', $session->classes_assigned ?? []) ?: 'All classes'],
    ] as [$label, $value])
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--lgr);font-size:13px;">
        <span style="color:var(--gr);">{{ $label }}</span>
        <span style="font-weight:600;">{{ $value }}</span>
      </div>
    @endforeach
    @if($session->admin_notes)
      <div style="padding:10px 0;font-size:13px;"><span style="color:var(--gr);">Notes: </span>{{ $session->admin_notes }}</div>
    @endif

    @if($session->childSessions->isNotEmpty())
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--bd);">
        <div style="font-size:12px;font-weight:700;color:var(--gr);margin-bottom:8px;">CHILD SESSIONS (Reopened from this one)</div>
        @foreach($session->childSessions as $child)
          <a href="{{ route('admin.sessions.show', $child) }}" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:var(--bl);">
            <span>{{ $child->session_code }}</span>
            <span class="badge {{ ['active'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$child->status] ?? 'bgr' }}">{{ $child->status }}</span>
          </a>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Checkups from this session --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Checkups in This Session</div></div>
    @forelse($session->checkups as $checkup)
      <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--lgr);">
        <div style="width:32px;height:32px;background:var(--g);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;">
          {{ strtoupper(substr($checkup->student->name,0,1)) }}
        </div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;">{{ $checkup->student->name }}</div>
          <div style="font-size:11px;color:var(--gr);">{{ $checkup->student->class_section }} · Score: {{ $checkup->overall_score ?? '—' }}</div>
        </div>
        <span class="badge {{ $checkup->status==='completed'?'bg':'by' }}">{{ ucfirst($checkup->status) }}</span>
        @if($checkup->alerts && count($checkup->alerts))
          <span class="badge br">{{ count($checkup->alerts) }} alert{{ count($checkup->alerts)>1?'s':'' }}</span>
        @endif
      </div>
    @empty
      <div style="text-align:center;padding:20px;color:var(--gr);font-size:13px;">No checkups recorded yet.</div>
    @endforelse
  </div>
</div>

{{-- Full checkup health records --}}
@if($session->checkups->isNotEmpty())
<div class="card" style="margin-bottom:18px;">
  <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
    <div class="card-title">Student Health Records</div>
    <div style="font-size:12px;color:var(--gr);">{{ $session->checkups->count() }} student{{ $session->checkups->count()>1?'s':'' }} · tap row to expand</div>
  </div>

  @php
    function ckVal($v, $unit='') { return ($v !== null && $v !== '') ? $v.($unit?' '.$unit:'') : '—'; }
  @endphp

  @foreach($session->checkups->sortBy('status') as $checkup)
    @php $cid = $checkup->id; @endphp

    {{-- Summary row --}}
    <div onclick="toggleCk({{ $cid }})" style="cursor:pointer;display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--lgr);user-select:none;">
      <div style="width:36px;height:36px;background:{{ $checkup->status==='completed'?'var(--g)':'var(--or)' }};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0;">
        {{ strtoupper(substr($checkup->student->name,0,1)) }}
      </div>
      <div style="flex:1;min-width:0;">
        <div style="font-size:13px;font-weight:700;">{{ $checkup->student->name }}</div>
        <div style="font-size:11px;color:var(--gr);">
          {{ $checkup->student->class_section }}
          · {{ $checkup->student->gender === 'M' ? 'Male' : 'Female' }}
          · Age {{ $checkup->student->age }}
          @if($checkup->student->blood_group) · {{ $checkup->student->blood_group }} @endif
        </div>
      </div>
      @if($checkup->overall_score)
        <div style="text-align:center;min-width:44px;">
          <div style="font-size:20px;font-weight:900;line-height:1;color:{{ $checkup->overall_score>=70?'var(--g)':($checkup->overall_score>=50?'var(--or)':'var(--r)') }};">{{ $checkup->overall_score }}</div>
          <div style="font-size:9px;color:var(--gr);letter-spacing:0.5px;">SCORE</div>
        </div>
      @endif
      <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;">
        <span class="badge {{ $checkup->status==='completed'?'bg':'by' }}">{{ ucfirst($checkup->status) }}</span>
        @if($checkup->alerts && count($checkup->alerts))
          <span class="badge br">{{ count($checkup->alerts) }} ⚠</span>
        @endif
        <span id="arr-{{ $cid }}" style="font-size:11px;color:var(--gr);transition:transform 0.2s;display:inline-block;">▼</span>
      </div>
    </div>

    {{-- Expanded detail panel --}}
    <div id="ck-{{ $cid }}" style="display:none;padding:14px 0 4px 0;">

      {{-- Physical Vitals --}}
      @php
        $vitals = array_filter([
          'Height'      => ckVal($checkup->height_cm, 'cm'),
          'Weight'      => ckVal($checkup->weight_kg, 'kg'),
          'BMI'         => ckVal($checkup->bmi),
          'Heart Rate'  => ckVal($checkup->heart_rate_bpm, 'bpm'),
          'BP Systolic' => ckVal($checkup->bp_systolic, 'mmHg'),
          'BP Diastolic'=> ckVal($checkup->bp_diastolic, 'mmHg'),
          'Temperature' => ckVal($checkup->temperature_f, '°F'),
          'SpO2'        => ckVal($checkup->spo2_percent, '%'),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($vitals))
        <div style="font-size:10px;font-weight:700;color:var(--gr);letter-spacing:1px;margin-bottom:8px;">PHYSICAL VITALS</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:14px;">
          @foreach($vitals as $label => $value)
            <div style="background:var(--lgr);border-radius:10px;padding:9px 11px;">
              <div style="font-size:10px;color:var(--gr);margin-bottom:3px;">{{ $label }}</div>
              <div style="font-size:14px;font-weight:700;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Sensory Health --}}
      @php
        $sensory = array_filter([
          'Vision (Left)'  => ckVal($checkup->vision_left),
          'Vision (Right)' => ckVal($checkup->vision_right),
          'Hearing'        => ckVal($checkup->hearing),
          'Eye Strain'     => ckVal($checkup->eye_strain),
          'Dental Score'   => $checkup->dental_score !== null ? $checkup->dental_score.'/10' : '—',
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($sensory))
        <div style="font-size:10px;font-weight:700;color:var(--gr);letter-spacing:1px;margin-bottom:8px;">SENSORY HEALTH</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:14px;">
          @foreach($sensory as $label => $value)
            <div style="background:var(--lgr);border-radius:10px;padding:9px 11px;">
              <div style="font-size:10px;color:var(--gr);margin-bottom:3px;">{{ $label }}</div>
              <div style="font-size:14px;font-weight:700;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Lab Tests --}}
      @php
        $labs = array_filter([
          'Haemoglobin'  => ckVal($checkup->haemoglobin_gdl, 'g/dL'),
          'Vitamin D'    => ckVal($checkup->vitamin_d_ngml, 'ng/mL'),
          'Iron Level'   => ckVal($checkup->iron_level),
          'Blood Sugar'  => ckVal($checkup->blood_sugar_mgdl, 'mg/dL'),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($labs))
        <div style="font-size:10px;font-weight:700;color:var(--gr);letter-spacing:1px;margin-bottom:8px;">LAB TESTS</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:14px;">
          @foreach($labs as $label => $value)
            <div style="background:var(--lgr);border-radius:10px;padding:9px 11px;">
              <div style="font-size:10px;color:var(--gr);margin-bottom:3px;">{{ $label }}</div>
              <div style="font-size:14px;font-weight:700;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Physical Fitness --}}
      @php
        $fitness = array_filter([
          'Posture'        => ckVal($checkup->posture),
          'Grip Strength'  => $checkup->grip_strength_score !== null ? $checkup->grip_strength_score.'/10' : '—',
          'Flexibility'    => ckVal($checkup->flexibility),
          'Flat Feet'      => ckVal($checkup->flat_feet),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($fitness))
        <div style="font-size:10px;font-weight:700;color:var(--gr);letter-spacing:1px;margin-bottom:8px;">PHYSICAL FITNESS</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:14px;">
          @foreach($fitness as $label => $value)
            <div style="background:var(--lgr);border-radius:10px;padding:9px 11px;">
              <div style="font-size:10px;color:var(--gr);margin-bottom:3px;">{{ $label }}</div>
              <div style="font-size:14px;font-weight:700;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Mental & Lifestyle --}}
      @php
        $mental = array_filter([
          'Mental Score'  => $checkup->mental_score !== null ? $checkup->mental_score.'/10' : '—',
          'Stress Level'  => ckVal($checkup->stress_level),
          'Sleep Quality' => ckVal($checkup->sleep_quality),
          'Skin Health'   => ckVal($checkup->skin_health),
          'Hair Health'   => ckVal($checkup->hair_health),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($mental))
        <div style="font-size:10px;font-weight:700;color:var(--gr);letter-spacing:1px;margin-bottom:8px;">MENTAL &amp; LIFESTYLE</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-bottom:14px;">
          @foreach($mental as $label => $value)
            <div style="background:var(--lgr);border-radius:10px;padding:9px 11px;">
              <div style="font-size:10px;color:var(--gr);margin-bottom:3px;">{{ $label }}</div>
              <div style="font-size:14px;font-weight:700;">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Alerts --}}
      @if($checkup->alerts && count($checkup->alerts))
        <div style="font-size:10px;font-weight:700;color:var(--r);letter-spacing:1px;margin-bottom:8px;">ALERTS</div>
        <div style="margin-bottom:14px;">
          @foreach($checkup->alerts as $alert)
            <div style="display:flex;align-items:flex-start;gap:8px;padding:7px 10px;background:#FEF2F2;border-radius:8px;margin-bottom:5px;font-size:12px;color:#991B1B;">
              <span style="flex-shrink:0;">⚠</span>
              <span>{{ $alert }}</span>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Doctor Notes & Recommendations --}}
      @if($checkup->doctor_notes || $checkup->recommendations)
        <div style="display:grid;grid-template-columns:{{ ($checkup->doctor_notes && $checkup->recommendations)?'1fr 1fr':'1fr' }};gap:10px;margin-bottom:10px;">
          @if($checkup->doctor_notes)
            <div style="background:#EFF6FF;border-radius:10px;padding:11px 13px;">
              <div style="font-size:10px;font-weight:700;color:#1E40AF;letter-spacing:1px;margin-bottom:5px;">DOCTOR NOTES</div>
              <div style="font-size:13px;color:#1E3A8A;line-height:1.5;">{{ $checkup->doctor_notes }}</div>
            </div>
          @endif
          @if($checkup->recommendations)
            <div style="background:#F0FDF4;border-radius:10px;padding:11px 13px;">
              <div style="font-size:10px;font-weight:700;color:#166534;letter-spacing:1px;margin-bottom:5px;">RECOMMENDATIONS</div>
              <div style="font-size:13px;color:#14532D;line-height:1.5;">{{ $checkup->recommendations }}</div>
            </div>
          @endif
        </div>
      @endif

      {{-- No data at all --}}
      @if($checkup->status === 'draft')
        <div style="text-align:center;padding:10px;font-size:12px;color:var(--gr);">This checkup is still a draft — data may be incomplete.</div>
      @endif

      <div style="border-top:1px solid var(--lgr);margin-top:4px;"></div>
    </div>
  @endforeach
</div>
@endif

{{-- Activity log for this session --}}
<div class="card">
  <div class="card-header"><div class="card-title">📋 Session Activity Log</div></div>
  <div class="tw">
    <table>
      <thead>
        <tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td style="white-space:nowrap;font-size:11px;">{{ $log->created_at->inDisplayTz()->format('d M H:i:s') }}</td>
            <td><strong>{{ $log->user->name }}</strong><br/><span class="badge {{ ['admin'=>'bp','doctor'=>'bb','parent'=>'bg'][$log->role] ?? 'bgr' }}">{{ $log->role }}</span></td>
            <td>{{ $log->action_label }}</td>
            <td style="font-size:12px;">{{ $log->description }}</td>
            <td style="font-size:11px;color:var(--gr);">{{ $log->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--gr);padding:20px;">No activity logged.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $logs->links() }}
</div>

@push('scripts')
<script>
document.querySelectorAll('[class*="hidden"]').forEach(el => {
  if(el.classList.contains('hidden')) el.style.display='none';
});

function toggleCk(id) {
  var panel = document.getElementById('ck-' + id);
  var arr   = document.getElementById('arr-' + id);
  if (!panel) return;
  var open = panel.style.display !== 'none';
  panel.style.display = open ? 'none' : 'block';
  if (arr) arr.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
@endpush
@endsection
