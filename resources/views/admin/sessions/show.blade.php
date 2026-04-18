@extends('layouts.app')
@section('title','Session Detail')
@section('page-title','Session Detail')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

{{-- Session header --}}
<div class="page-header page-header--top mb-18">
  <div>
    <div class="page-header__eyebrow">Doctor Session</div>
    <div class="page-header__title">{{ $session->session_code }}</div>
    <div class="page-header__sub">
      Dr. {{ $session->doctor->name }} ({{ $session->doctor->staff_code }}) ·
      {{ $session->school_name }}, {{ $session->school_city }} ·
      {{ $session->visit_date->format('d M Y') }}
    </div>
    @if($session->is_reopened)
      <div class="page-header__meta">
        <span class="badge by">Reopened from Session #{{ $session->parent_session_id }}</span>
      </div>
    @endif
    @if($session->parentSession)
      <div class="page-header__meta meta">
        Linked to: <a href="{{ route('admin.sessions.show', $session->parentSession) }}" class="text-green">{{ $session->parentSession->session_code }}</a>
      </div>
    @endif
  </div>
  <div class="page-header__actions">
    @php $badge = ['active'=>'bb','pending'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$session->status_badge] ?? 'bgr'; @endphp
    <span class="badge {{ $badge }}">{{ ucfirst($session->status_badge) }}</span>

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

{{-- Reopen form (hidden by default via .hidden CSS class) --}}
<div id="reopen-box" class="hidden card mb-18" style="background:#FFFBEB;">
  <div class="card-header"><div class="card-title">🔄 Reopen Session — New Code Will Be Generated</div></div>
  <div class="alert alert-y mb-16">
    <span>⚠️</span>
    <span class="fs-13">A <strong>brand new session code</strong> will be generated and linked to this session. The old code <strong>cannot be reused</strong>. Share the new code with the doctor.</span>
  </div>
  <form method="POST" action="{{ route('admin.sessions.reopen', $session) }}">
    @csrf
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label">New Visit Date <span class="req">*</span></label>
        <input type="date" name="visit_date" class="form-input" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required />
      </div>
      <div class="form-group">
        <label class="form-label">Reason / Notes</label>
        <input type="text" name="admin_notes" class="form-input" placeholder="Reason for reopening…" />
      </div>
    </div>
    <button type="submit" class="btn btn-or btn-lg">Generate New Session Code →</button>
  </form>
</div>

{{-- Stats row --}}
<div class="stat-grid mb-18">
  <div class="scard"><div class="sc-l">Checkups Done</div><div class="sc-v sc-v--green">{{ $session->checkups->where('status','completed')->count() }}</div></div>
  <div class="scard"><div class="sc-l">Drafts</div><div class="sc-v sc-v--orange">{{ $session->checkups->where('status','draft')->count() }}</div></div>
  <div class="scard"><div class="sc-l">Total Alerts</div><div class="sc-v sc-v--red">{{ $session->checkups->flatMap(fn($c)=>$c->alerts??[])->count() }}</div></div>
  <div class="scard"><div class="sc-l">Session Duration</div><div class="sc-v" style="font-size:20px;">{{ $session->duration_hours }}h</div></div>
</div>

<div class="g2">
  {{-- Session info --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Session Details</div></div>
    @foreach([
      ['Created By',      $session->createdByAdmin->name . ' (Admin)'],
      ['Created At',      $session->created_at->inDisplayTz()->format('d M Y H:i')],
      ['Activated At',    $session->activated_at?->inDisplayTz()->format('d M Y H:i') ?? 'Not yet activated'],
      ['Last Activity',   $session->last_activity_at?->inDisplayTz()->format('d M Y H:i') ?? '—'],
      ['Expires At',      $session->expires_at->inDisplayTz()->format('d M Y H:i')],
      ['Classes Assigned',implode(', ', $session->classes_assigned ?? []) ?: 'All classes'],
    ] as [$label, $value])
      <div class="detail-row">
        <span class="detail-label">{{ $label }}</span>
        <span class="detail-value">{{ $value }}</span>
      </div>
    @endforeach
    @if($session->admin_notes)
      <div class="detail-row">
        <span class="detail-label">Notes</span>
        <span class="detail-value">{{ $session->admin_notes }}</span>
      </div>
    @endif

    @if($session->childSessions->isNotEmpty())
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--bd);">
        <div class="section-label">Child Sessions (Reopened from this one)</div>
        @foreach($session->childSessions as $child)
          <a href="{{ route('admin.sessions.show', $child) }}" class="detail-row text-blue">
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
      <div class="list-row">
        <div class="avatar avatar--sm {{ $checkup->status === 'completed' ? 'avatar--green' : 'avatar--orange' }}">
          {{ strtoupper(substr($checkup->student->name, 0, 1)) }}
        </div>
        <div class="flex-auto">
          <div class="list-row__title">{{ $checkup->student->name }}</div>
          <div class="list-row__sub">{{ $checkup->student->class_section }} · Score: {{ $checkup->overall_score ?? '—' }}</div>
        </div>
        <span class="badge {{ $checkup->status === 'completed' ? 'bg' : 'by' }}">{{ ucfirst($checkup->status) }}</span>
        @if($checkup->alerts && count($checkup->alerts))
          <span class="badge br">{{ count($checkup->alerts) }} alert{{ count($checkup->alerts) > 1 ? 's' : '' }}</span>
        @endif
      </div>
    @empty
      <div class="empty-state">No checkups recorded yet.</div>
    @endforelse
  </div>
</div>

{{-- Full student health records accordion --}}
@if($session->checkups->isNotEmpty())
<div class="card mb-18">
  <div class="card-header">
    <div class="card-title">Student Health Records</div>
    <div class="meta">{{ $session->checkups->count() }} student{{ $session->checkups->count() > 1 ? 's' : '' }} · tap row to expand</div>
  </div>

  @php
    function ckVal($v, $unit='') { return ($v !== null && $v !== '') ? $v . ($unit ? ' '.$unit : '') : '—'; }
  @endphp

  @foreach($session->checkups->sortBy('status') as $checkup)
    @php $cid = $checkup->id; @endphp

    {{-- Summary row (clickable) --}}
    <div onclick="toggleCk({{ $cid }})" class="list-row list-row--clickable">
      <div class="avatar avatar--md {{ $checkup->status === 'completed' ? 'avatar--green' : 'avatar--orange' }}">
        {{ strtoupper(substr($checkup->student->name, 0, 1)) }}
      </div>
      <div class="flex-auto">
        <div class="list-row__title">{{ $checkup->student->name }}</div>
        <div class="list-row__sub">
          {{ $checkup->student->class_section }}
          · {{ $checkup->student->gender === 'M' ? 'Male' : 'Female' }}
          · Age {{ $checkup->student->age }}
          @if($checkup->student->blood_group) · {{ $checkup->student->blood_group }} @endif
        </div>
      </div>
      @if($checkup->overall_score)
        <div class="text-center" style="min-width:44px;">
          <div style="font-size:20px;font-weight:900;line-height:1;color:{{ $checkup->overall_score>=70?'var(--g)':($checkup->overall_score>=50?'var(--or)':'var(--r)') }};">{{ $checkup->overall_score }}</div>
          <div class="meta" style="letter-spacing:0.5px;">SCORE</div>
        </div>
      @endif
      <div class="d-flex" style="gap:6px;align-items:center;flex-shrink:0;">
        <span class="badge {{ $checkup->status === 'completed' ? 'bg' : 'by' }}">{{ ucfirst($checkup->status) }}</span>
        @if($checkup->alerts && count($checkup->alerts))
          <span class="badge br">{{ count($checkup->alerts) }} ⚠</span>
        @endif
        <span id="arr-{{ $cid }}" class="meta" style="transition:transform 0.2s;display:inline-block;">▼</span>
      </div>
    </div>

    {{-- Expanded detail panel --}}
    <div id="ck-{{ $cid }}" style="display:none;padding:14px 0 4px 0;">

      {{-- Physical Vitals --}}
      @php
        $vitals = array_filter([
          'Height'       => ckVal($checkup->height_cm, 'cm'),
          'Weight'       => ckVal($checkup->weight_kg, 'kg'),
          'BMI'          => ckVal($checkup->bmi),
          'Heart Rate'   => ckVal($checkup->heart_rate_bpm, 'bpm'),
          'BP Systolic'  => ckVal($checkup->bp_systolic, 'mmHg'),
          'BP Diastolic' => ckVal($checkup->bp_diastolic, 'mmHg'),
          'Temperature'  => ckVal($checkup->temperature_f, '°F'),
          'SpO2'         => ckVal($checkup->spo2_percent, '%'),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($vitals))
        <div class="section-label">Physical Vitals</div>
        <div class="data-grid">
          @foreach($vitals as $label => $value)
            <div class="data-tile">
              <div class="data-tile__label">{{ $label }}</div>
              <div class="data-tile__value">{{ $value }}</div>
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
        <div class="section-label">Sensory Health</div>
        <div class="data-grid">
          @foreach($sensory as $label => $value)
            <div class="data-tile">
              <div class="data-tile__label">{{ $label }}</div>
              <div class="data-tile__value">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Lab Tests --}}
      @php
        $labs = array_filter([
          'Haemoglobin' => ckVal($checkup->haemoglobin_gdl, 'g/dL'),
          'Vitamin D'   => ckVal($checkup->vitamin_d_ngml, 'ng/mL'),
          'Iron Level'  => ckVal($checkup->iron_level),
          'Blood Sugar' => ckVal($checkup->blood_sugar_mgdl, 'mg/dL'),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($labs))
        <div class="section-label">Lab Tests</div>
        <div class="data-grid">
          @foreach($labs as $label => $value)
            <div class="data-tile">
              <div class="data-tile__label">{{ $label }}</div>
              <div class="data-tile__value">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Physical Fitness --}}
      @php
        $fitness = array_filter([
          'Posture'       => ckVal($checkup->posture),
          'Grip Strength' => $checkup->grip_strength_score !== null ? $checkup->grip_strength_score.'/10' : '—',
          'Flexibility'   => ckVal($checkup->flexibility),
          'Flat Feet'     => ckVal($checkup->flat_feet),
        ], fn($v) => $v !== '—');
      @endphp
      @if(count($fitness))
        <div class="section-label">Physical Fitness</div>
        <div class="data-grid">
          @foreach($fitness as $label => $value)
            <div class="data-tile">
              <div class="data-tile__label">{{ $label }}</div>
              <div class="data-tile__value">{{ $value }}</div>
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
        <div class="section-label">Mental &amp; Lifestyle</div>
        <div class="data-grid">
          @foreach($mental as $label => $value)
            <div class="data-tile">
              <div class="data-tile__label">{{ $label }}</div>
              <div class="data-tile__value">{{ $value }}</div>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Alerts --}}
      @if($checkup->alerts && count($checkup->alerts))
        <div class="section-label section-label--red">Alerts</div>
        <div style="margin-bottom:14px;">
          @foreach($checkup->alerts as $alert)
            <div class="alert-item">
              <span class="alert-item__icon">⚠</span>
              <span>{{ $alert }}</span>
            </div>
          @endforeach
        </div>
      @endif

      {{-- Doctor Notes & Recommendations --}}
      @if($checkup->doctor_notes || $checkup->recommendations)
        <div class="note-grid {{ ($checkup->doctor_notes && $checkup->recommendations) ? 'note-grid--double' : '' }} mb-16">
          @if($checkup->doctor_notes)
            <div class="note-card note-card--notes">
              <div class="note-card__label">Doctor Notes</div>
              <div class="note-card__text">{{ $checkup->doctor_notes }}</div>
            </div>
          @endif
          @if($checkup->recommendations)
            <div class="note-card note-card--rec">
              <div class="note-card__label">Recommendations</div>
              <div class="note-card__text">{{ $checkup->recommendations }}</div>
            </div>
          @endif
        </div>
      @endif

      @if($checkup->status === 'draft')
        <div class="empty-state fs-12">This checkup is still a draft — data may be incomplete.</div>
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
            <td class="meta" style="white-space:nowrap;">{{ $log->created_at->inDisplayTz()->format('d M H:i:s') }}</td>
            <td>
              <strong>{{ $log->user->name }}</strong><br />
              <span class="badge {{ ['admin'=>'bp','doctor'=>'bb','parent'=>'bg'][$log->role] ?? 'bgr' }}">{{ $log->role }}</span>
            </td>
            <td>{{ $log->action_label }}</td>
            <td class="fs-12">{{ $log->description }}</td>
            <td class="meta">{{ $log->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty-state">No activity logged.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination-wrap">{{ $logs->links() }}</div>
</div>

@push('scripts')
<script src="{{ asset('js/session-show.js') }}"></script>
@endpush

@endsection
