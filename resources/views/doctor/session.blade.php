@extends('layouts.app')
@section('title', 'Checkup Session')
@section('page-title', 'Active Checkup Session')

@section('sidebar-nav')
<a href="{{ route('doctor.session.active') }}" class="ni {{ request()->routeIs('doctor.session.active') ? 'active' : '' }}" style="background:rgba(59,130,246,0.15);color:#fff;">
  <div class="ni-ico" style="background:rgba(59,130,246,0.3);">🩺</div> Checkup Session
</a>
<a href="{{ route('doctor.completed') }}" class="ni {{ request()->routeIs('doctor.completed') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(29,158,117,0.2);">✅</div> Completed Today
</a>
<a href="{{ route('doctor.summary') }}" class="ni {{ request()->routeIs('doctor.summary') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(245,158,11,0.2);">📊</div> Session Summary
</a>
@endsection

@section('content')

{{-- Session banner --}}
<div class="session-banner">
  <div>
    <div class="session-banner__label">Active Session</div>
    <div class="session-banner__title">
      {{ $doctorSession->school_name }}
      @if($doctorSession->classes_assigned) — {{ implode(', ', $doctorSession->classes_assigned) }} @endif
    </div>
    <div class="session-banner__sub">
      {{ $doctorSession->visit_date->format('d M Y') }} · Code: {{ $doctorSession->session_code }} · Expires: {{ $doctorSession->expires_at->inDisplayTz()->format('H:i') }}
    </div>
  </div>
  <div class="session-counters">
    @php $done = $completedIds->count(); $total = $students->count(); @endphp
    <div class="session-counter">
      <div class="session-counter__num" style="color:#4ADE80;">{{ $done }}</div>
      <div class="session-counter__label">Done</div>
    </div>
    <div class="session-counter">
      <div class="session-counter__num" style="color:#FCD34D;">{{ $total - $done }}</div>
      <div class="session-counter__label">Pending</div>
    </div>
    <div class="session-counter">
      <div class="session-counter__num" style="color:#fff;">{{ $total }}</div>
      <div class="session-counter__label">Total</div>
    </div>
  </div>
</div>

{{-- Progress bar --}}
<div class="progress-track">
  <div class="progress-fill" style="width:{{ $total > 0 ? round(($done / $total) * 100) : 0 }}%;"></div>
</div>

{{-- Student grid grouped by class --}}
@php $classes = $students->groupBy('class_section'); @endphp

@foreach($classes as $class => $classStudents)
  <div class="card mb-16">
    <div class="card-header">
      <div class="card-title">Class {{ $class }} &nbsp;·&nbsp; {{ $classStudents->count() }} students</div>
      <span class="fw-600 text-green fs-12">
        {{ $classStudents->filter(fn($s) => $completedIds->contains($s->id))->count() }} / {{ $classStudents->count() }} done
      </span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
      @foreach($classStudents as $student)
        @php
          $isDone    = $completedIds->contains($student->id);
          $checkup   = $student->checkups->first();
          $hasDraft  = $checkup && $checkup->status === 'draft';
          $hasAlerts = $isDone && $checkup && count($checkup->alerts ?? []) > 0;
        @endphp
        <a href="{{ route('doctor.checkup.form', $student) }}"
           class="student-card {{ $isDone ? 'student-card--done' : ($hasDraft ? 'student-card--draft' : '') }}">
          <div class="avatar avatar--md {{ $student->gender === 'M' ? 'avatar--male' : 'avatar--female' }}">
            {{ strtoupper(substr($student->name, 0, 1)) }}
          </div>
          <div class="flex-auto">
            <div class="fw-600 fs-13" style="color:var(--dk);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $student->name }}</div>
            <div class="meta">{{ $student->gender === 'M' ? 'Boy' : 'Girl' }} · Age {{ $student->age }}</div>
          </div>
          <div style="flex-shrink:0;text-align:right;">
            @if($isDone)
              <span style="font-size:16px;">✅</span>
              @if($checkup?->overall_score)
                <div class="fw-700" style="font-size:10px;color:{{ $checkup->overall_score >= 75 ? 'var(--g)' : ($checkup->overall_score >= 55 ? 'var(--or)' : 'var(--r)') }};">{{ $checkup->overall_score }}</div>
              @endif
              @if($hasAlerts)
                <span class="badge br" style="font-size:9px;">{{ count($checkup->alerts) }}⚠</span>
              @endif
            @elseif($hasDraft)
              <span style="font-size:12px;">📝</span>
              <div class="fw-700 text-orange" style="font-size:9px;">DRAFT</div>
            @else
              <span class="text-muted" style="font-size:16px;">○</span>
            @endif
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endforeach

@if($students->isEmpty())
  <div class="card empty-state--lg text-center">
    <div style="font-size:48px;margin-bottom:16px;opacity:.4;">👥</div>
    <div style="font-size:16px;font-weight:700;" class="text-muted">No students found for this session</div>
    <div class="meta mt-8">Students matching school "{{ $doctorSession->school_name }}" will appear here once added by admin.</div>
  </div>
@endif

@endsection
