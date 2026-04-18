@extends('layouts.app')
@section('title', $student->name)
@section('page-title', 'Student Profile')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

@php
  $typeColors = [
    'general_physician' => '#3B82F6',
    'dentist'           => '#8B5CF6',
    'eye_specialist'    => '#06B6D4',
    'audiologist_ent'   => '#F59E0B',
    'physiotherapist'   => '#10B981',
    'psychologist'      => '#EC4899',
    'lab_technician'    => '#EF4444',
  ];
@endphp

{{-- Header --}}
<div class="page-header mb-18">
  <div class="page-header__left">
    <div class="avatar avatar--xl {{ $student->gender === 'M' ? 'avatar--male' : 'avatar--female' }}">
      {{ strtoupper(substr($student->name, 0, 1)) }}
    </div>
    <div class="page-header__body">
      <div class="page-header__title">{{ $student->name }}</div>
      <div class="page-header__sub">{{ $student->school_name }} · Class {{ $student->class_section }}</div>
      <div class="page-header__meta">
        <code style="font-size:12px;background:rgba(255,255,255,0.1);color:#4ADE80;padding:3px 10px;border-radius:6px;font-weight:700;">{{ $student->reference_code }}</code>
        <span class="badge {{ $student->is_active ? 'bg' : 'br' }}" style="margin-left:6px;">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
      </div>
    </div>
  </div>
  <a href="{{ route('admin.students') }}" class="btn btn-back">← All Students</a>
</div>

{{-- Stats --}}
@php
  $completed = $student->checkups->where('status', 'completed');
  $latest    = $completed->first();
@endphp
<div class="stat-grid mb-18">
  <div class="scard">
    <div class="sc-l">Age</div>
    <div class="sc-v">{{ $student->age }}</div>
    <div class="sc-s">Years</div>
  </div>
  <div class="scard">
    <div class="sc-l">Checkups Done</div>
    <div class="sc-v sc-v--green">{{ $completed->count() }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Latest Score</div>
    @if($latest)
      <div class="sc-v" style="color:{{ $latest->overall_score >= 70 ? 'var(--g)' : ($latest->overall_score >= 50 ? 'var(--or)' : 'var(--r)') }};">{{ $latest->overall_score }}</div>
    @else
      <div class="sc-v sc-v--muted">—</div>
    @endif
    <div class="sc-s">/100</div>
  </div>
  <div class="scard">
    <div class="sc-l">Total Alerts</div>
    <div class="sc-v sc-v--red">{{ $student->checkups->flatMap(fn($c) => $c->alerts ?? [])->count() }}</div>
  </div>
</div>

<div class="g2">

  {{-- Left: Student info + parent card --}}
  <div style="display:flex;flex-direction:column;gap:14px;">

    <div class="card">
      <div class="card-header"><div class="card-title">Student Information</div></div>
      @foreach([
        ['Reference Code',  $student->reference_code],
        ['Full Name',        $student->name],
        ['Gender',           $student->gender === 'M' ? 'Male' : ($student->gender === 'F' ? 'Female' : 'Other')],
        ['Date of Birth',    $student->date_of_birth->format('d F Y')],
        ['Age',              $student->age . ' years'],
        ['Class / Section',  $student->class_section],
        ['School',           $student->school_name],
        ['City',             $student->school_city],
        ['Blood Group',      $student->blood_group ?: '—'],
        ['Known Conditions', $student->known_conditions ?: 'None'],
        ['Status',           $student->is_active ? 'Active' : 'Inactive'],
        ['Registered',       $student->created_at->inDisplayTz()->format('d M Y')],
      ] as [$label, $value])
        <div class="detail-row">
          <span class="detail-label">{{ $label }}</span>
          <span class="detail-value">{{ $value }}</span>
        </div>
      @endforeach
    </div>

    {{-- Parent card --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">Parent / Guardian</div>
        <a href="{{ route('admin.parents.show', $student->parent) }}" class="btn btn-out btn-sm">View Parent →</a>
      </div>
      <div class="list-row">
        <div class="avatar avatar--ml avatar--green">
          {{ strtoupper(substr($student->parent->name, 0, 1)) }}
        </div>
        <div>
          <div class="fw-700" style="font-size:14px;">{{ $student->parent->name }}</div>
          <div class="meta">{{ $student->parent->email ?? '—' }}</div>
        </div>
      </div>
      @foreach([
        ['Phone',  $student->parent->phone ?? '—'],
        ['Email',  $student->parent->email ?? '—'],
        ['Status', $student->parent->is_active ? 'Active' : 'Inactive'],
      ] as [$label, $value])
        <div class="detail-row">
          <span class="detail-label">{{ $label }}</span>
          <span class="detail-value">{{ $value }}</span>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Right: Checkup history (multi-specialist) --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Checkup History</div><div class="meta">{{ $completed->count() }} checkup{{ $completed->count() != 1 ? 's' : '' }}</div></div>

    @forelse($completed as $c)
      @php
        $drType   = $c->doctor?->doctor_type;
        $drLabel  = $drType ? (\App\Models\User::DOCTOR_TYPES[$drType] ?? $drType) : null;
        $drColor  = $typeColors[$drType] ?? '#6B7280';
      @endphp
      <div style="padding:14px 0;border-bottom:1px solid var(--lgr);">
        <div class="list-row" style="padding:0;border:none;">
          {{-- Score circle --}}
          <div style="width:44px;height:44px;border-radius:12px;background:{{ $c->overall_score>=70?'rgba(29,158,117,0.15)':($c->overall_score>=50?'rgba(249,115,22,0.15)':'rgba(239,68,68,0.15)') }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <div class="fw-700" style="font-size:16px;color:{{ $c->overall_score>=70?'var(--g)':($c->overall_score>=50?'var(--or)':'var(--r)') }};">{{ $c->overall_score ?: '—' }}</div>
          </div>
          <div class="flex-auto">
            <div class="fw-700 fs-13">{{ $c->checkup_date->format('d M Y') }}</div>
            <div class="meta" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
              <span>Dr. {{ $c->doctor->name ?? '—' }}</span>
              @if($drLabel)
                <span style="display:inline-block;font-size:10px;font-weight:700;background:{{ $drColor }}18;color:{{ $drColor }};padding:1px 6px;border-radius:12px;border:1px solid {{ $drColor }}33;">{{ $drLabel }}</span>
              @endif
              @if($c->bmi) <span>· BMI {{ $c->bmi }}</span> @endif
            </div>
          </div>
          @if($c->alerts && count($c->alerts))
            <span class="badge br">{{ count($c->alerts) }} alert{{ count($c->alerts) > 1 ? 's' : '' }}</span>
          @else
            <span class="badge bg">Clear</span>
          @endif
        </div>

        {{-- Alerts --}}
        @if($c->alerts && count($c->alerts))
          <div style="margin-top:8px;margin-left:56px;">
            @foreach($c->alerts as $alert)
              <div class="inline-alert">⚠ {{ $alert }}</div>
            @endforeach
          </div>
        @endif

        {{-- Parameters by section --}}
        @php
          $sections = \App\Models\User::DOCTOR_TYPE_SECTIONS[$drType] ?? [];
        @endphp
        <div style="margin-top:10px;margin-left:56px;display:flex;flex-wrap:wrap;gap:8px;">
          @if(in_array('physical', $sections) && $c->height_cm)
            <div class="inline-stat">📏 {{ $c->height_cm }}cm · {{ $c->weight_kg }}kg @if($c->bmi) · BMI {{ $c->bmi }} @endif</div>
          @endif
          @if(in_array('dental', $sections) && $c->dental_score)
            <div class="inline-stat">🦷 Dental: {{ $c->dental_score }}/10</div>
          @endif
          @if(in_array('eye', $sections) && ($c->vision_left || $c->vision_right))
            <div class="inline-stat">👁️ L: {{ $c->vision_left ?? '—' }} · R: {{ $c->vision_right ?? '—' }}</div>
          @endif
          @if(in_array('hearing', $sections) && $c->hearing)
            <div class="inline-stat">👂 {{ $c->hearing }}</div>
          @endif
          @if(in_array('musculoskeletal', $sections) && $c->posture)
            <div class="inline-stat">🦴 {{ $c->posture }} · Grip: {{ $c->grip_strength_score ?? '—' }}/10</div>
          @endif
          @if(in_array('mental', $sections) && $c->mental_score)
            <div class="inline-stat">🧠 Mental: {{ $c->mental_score }}/10 · Stress: {{ $c->stress_level ?? '—' }}</div>
          @endif
          @if(in_array('lab', $sections) && $c->haemoglobin_gdl)
            <div class="inline-stat">🧪 Hb: {{ $c->haemoglobin_gdl }} · VitD: {{ $c->vitamin_d_ngml ?? '—' }}</div>
          @endif
        </div>

        {{-- Notes/Recommendations --}}
        @if($c->doctor_notes || $c->recommendations)
          <div style="margin-top:8px;margin-left:56px;display:flex;flex-direction:column;gap:4px;">
            @if($c->doctor_notes)
              <div class="doctor-note-italic">"{{ $c->doctor_notes }}"</div>
            @endif
            @if($c->recommendations)
              <div style="font-size:12px;color:var(--g);"><strong>→</strong> {{ $c->recommendations }}</div>
            @endif
          </div>
        @endif
      </div>
    @empty
      <div class="empty-state">No completed checkups yet.</div>
    @endforelse
  </div>

</div>

@push('head')
<style>
.inline-stat {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  background: var(--lgr);
  color: var(--dk);
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid var(--bd);
}
</style>
@endpush

@endsection
