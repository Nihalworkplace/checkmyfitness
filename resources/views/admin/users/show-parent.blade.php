@extends('layouts.app')
@section('title', $parent->name)
@section('page-title', 'Parent Profile')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

{{-- Header --}}
<div class="page-header mb-18">
  <div class="page-header__left">
    <div class="avatar avatar--xl avatar--green">
      {{ strtoupper(substr($parent->name, 0, 1)) }}
    </div>
    <div class="page-header__body">
      <div class="page-header__title">{{ $parent->name }}</div>
      <div class="page-header__sub">
        {{ $parent->email ?? '—' }}
        @if($parent->phone) · {{ $parent->phone }} @endif
      </div>
      <div class="page-header__meta">
        <span class="badge {{ $parent->is_active ? 'bg' : 'br' }}">{{ $parent->is_active ? 'Active' : 'Inactive' }}</span>
      </div>
    </div>
  </div>
  <a href="{{ route('admin.parents') }}" class="btn btn-back">← All Parents</a>
</div>

{{-- Stats --}}
@php
  $allCheckups = $parent->students->flatMap(fn($s) => $s->checkups->where('status', 'completed'));
  $avgScore    = $allCheckups->avg('overall_score');
  $totalAlerts = $allCheckups->flatMap(fn($c) => $c->alerts ?? [])->count();
@endphp
<div class="stat-grid mb-18">
  <div class="scard">
    <div class="sc-l">Children</div>
    <div class="sc-v sc-v--blue">{{ $parent->students->count() }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Total Checkups</div>
    <div class="sc-v sc-v--green">{{ $allCheckups->count() }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Avg Health Score</div>
    <div class="sc-v" style="color:{{ $avgScore >= 70 ? 'var(--g)' : ($avgScore >= 50 ? 'var(--or)' : 'var(--r)') }};">
      {{ $avgScore ? round($avgScore) : '—' }}
    </div>
  </div>
  <div class="scard">
    <div class="sc-l">Total Alerts</div>
    <div class="sc-v sc-v--red">{{ $totalAlerts }}</div>
  </div>
</div>

<div class="g2">

  {{-- Parent details --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Parent Information</div></div>
    @foreach([
      ['Full Name',  $parent->name],
      ['Email',      $parent->email ?? '—'],
      ['Phone',      $parent->phone ?? '—'],
      ['Status',     $parent->is_active ? 'Active' : 'Inactive'],
      ['Registered', $parent->created_at->inDisplayTz()->format('d M Y')],
      ['Children',   $parent->students->count() . ' student' . ($parent->students->count() != 1 ? 's' : '')],
    ] as [$label, $value])
      <div class="detail-row">
        <span class="detail-label">{{ $label }}</span>
        <span class="detail-value">{{ $value }}</span>
      </div>
    @endforeach
  </div>

  {{-- Children list with health snapshot --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Children &amp; Health Snapshot</div></div>

    @forelse($parent->students as $student)
      @php $latest = $student->checkups->where('status', 'completed')->first(); @endphp
      <div style="padding:13px 0;border-bottom:1px solid var(--lgr);">
        <div class="list-row" style="padding:0;border:none;">
          <div class="avatar avatar--ml {{ $student->gender === 'M' ? 'avatar--male' : 'avatar--female' }}">
            {{ strtoupper(substr($student->name, 0, 1)) }}
          </div>
          <div class="flex-auto">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <span class="fw-700 fs-13">{{ $student->name }}</span>
              <span class="badge {{ $student->is_active ? 'bg' : 'br' }}">{{ $student->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="meta" style="margin-top:2px;">
              Class {{ $student->class_section }} · {{ $student->school_name }} · Age {{ $student->age }}
              · {{ $student->gender === 'M' ? 'Male' : ($student->gender === 'F' ? 'Female' : 'Other') }}
              @if($student->blood_group) · {{ $student->blood_group }} @endif
            </div>
            <div style="margin-top:4px;">
              <code class="code-pill text-muted">{{ $student->reference_code }}</code>
            </div>
          </div>
          @if($latest)
            <div class="text-center" style="flex-shrink:0;">
              <div class="fw-700" style="font-size:20px;color:{{ $latest->overall_score>=70?'var(--g)':($latest->overall_score>=50?'var(--or)':'var(--r)') }};">{{ $latest->overall_score }}</div>
              <div class="meta" style="letter-spacing:0.5px;">SCORE</div>
            </div>
          @else
            <div class="meta">No checkup</div>
          @endif
          <a href="{{ route('admin.students.show', $student) }}" class="btn btn-out btn-sm">View</a>
        </div>

        {{-- Latest checkup mini-summary --}}
        @if($latest)
          <div style="margin-top:8px;margin-left:52px;display:flex;flex-wrap:wrap;gap:6px;">
            @if($latest->height_cm)
              <span class="meta" style="background:var(--lgr);border-radius:6px;padding:3px 8px;">{{ $latest->height_cm }}cm</span>
            @endif
            @if($latest->weight_kg)
              <span class="meta" style="background:var(--lgr);border-radius:6px;padding:3px 8px;">{{ $latest->weight_kg }}kg</span>
            @endif
            @if($latest->bmi)
              <span class="meta" style="background:var(--lgr);border-radius:6px;padding:3px 8px;">BMI {{ $latest->bmi }}</span>
            @endif
            @if($latest->haemoglobin_gdl)
              <span class="meta" style="background:var(--lgr);border-radius:6px;padding:3px 8px;">Hb {{ $latest->haemoglobin_gdl }}</span>
            @endif
            <span class="meta" style="padding:3px 0;">{{ $latest->checkup_date->format('d M Y') }}</span>
            @if($latest->alerts && count($latest->alerts))
              <span class="badge br">{{ count($latest->alerts) }} alert{{ count($latest->alerts) > 1 ? 's' : '' }}</span>
            @endif
          </div>
        @endif
      </div>
    @empty
      <div class="empty-state">No children linked to this parent.</div>
    @endforelse
  </div>

</div>

@endsection
