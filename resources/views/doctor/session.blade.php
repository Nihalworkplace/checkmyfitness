@extends('layouts.app')
@section('title','Checkup Session')
@section('page-title','Active Checkup Session')

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
<div style="background:linear-gradient(135deg,#1E40AF,#3B82F6);border-radius:16px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
  <div>
    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,0.5);letter-spacing:1.5px;text-transform:uppercase;">Active Session</div>
    <div style="font-family:'Fraunces',serif;font-size:18px;font-weight:900;color:#fff;margin-top:2px;">{{ $doctorSession->school_name }}
      @if($doctorSession->classes_assigned)— {{ implode(', ', $doctorSession->classes_assigned) }}@endif
    </div>
    <div style="font-size:12px;color:rgba(255,255,255,0.55);">{{ $doctorSession->visit_date->format('d M Y') }} · Code: {{ $doctorSession->session_code }} · Expires: {{ $doctorSession->expires_at->inDisplayTz()->format('H:i') }}</div>
  </div>
  <div style="display:flex;gap:20px;">
    @php $done=$completedIds->count(); $total=$students->count(); @endphp
    <div style="text-align:center;"><div style="font-family:'Fraunces',serif;font-size:26px;font-weight:900;color:#4ADE80;">{{ $done }}</div><div style="font-size:10px;color:rgba(255,255,255,0.45);">Done</div></div>
    <div style="text-align:center;"><div style="font-family:'Fraunces',serif;font-size:26px;font-weight:900;color:#FCD34D;">{{ $total - $done }}</div><div style="font-size:10px;color:rgba(255,255,255,0.45);">Pending</div></div>
    <div style="text-align:center;"><div style="font-family:'Fraunces',serif;font-size:26px;font-weight:900;color:#fff;">{{ $total }}</div><div style="font-size:10px;color:rgba(255,255,255,0.45);">Total</div></div>
  </div>
</div>

{{-- Progress bar --}}
<div style="background:#E2EDE9;border-radius:8px;height:8px;margin-bottom:16px;overflow:hidden;">
  <div style="width:{{ $total > 0 ? round(($done/$total)*100) : 0 }}%;height:100%;background:var(--g);border-radius:8px;transition:width .4s;"></div>
</div>

{{-- Student grid --}}
@php $classes = $students->groupBy('class_section'); @endphp

@foreach($classes as $class => $classStudents)
  <div class="card" style="margin-bottom:16px;">
    <div class="card-header">
      <div class="card-title">Class {{ $class }} &nbsp;·&nbsp; {{ $classStudents->count() }} students</div>
      <span style="font-size:12px;color:var(--g);font-weight:600;">{{ $classStudents->filter(fn($s)=>$completedIds->contains($s->id))->count() }} / {{ $classStudents->count() }} done</span>
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
           style="display:flex;align-items:center;gap:10px;padding:12px;border-radius:12px;border:1.5px solid {{ $isDone ? 'var(--g)' : ($hasDraft ? 'var(--or)' : 'var(--bd)') }};background:{{ $isDone ? '#F0FDF9' : ($hasDraft ? '#FFFBEB' : '#fff') }};text-decoration:none;transition:all .18s;"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
          <div style="width:36px;height:36px;border-radius:10px;background:{{ $student->gender==='M' ? '#3B82F6' : '#8B5CF6' }};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff;flex-shrink:0;">
            {{ strtoupper(substr($student->name,0,1)) }}
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--dk);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $student->name }}</div>
            <div style="font-size:10px;color:var(--gr);">{{ $student->gender==='M'?'Boy':'Girl' }} · Age {{ $student->age }}</div>
          </div>
          <div style="flex-shrink:0;text-align:right;">
            @if($isDone)
              <span style="font-size:16px;">✅</span>
              @if($checkup?->overall_score)
                <div style="font-size:10px;font-weight:700;color:{{ $checkup->overall_score >= 75 ? 'var(--g)' : ($checkup->overall_score >= 55 ? 'var(--or)' : 'var(--r)') }};">{{ $checkup->overall_score }}</div>
              @endif
              @if($hasAlerts)
                <span class="badge br" style="font-size:9px;">{{ count($checkup->alerts) }}⚠</span>
              @endif
            @elseif($hasDraft)
              <span style="font-size:12px;">📝</span>
              <div style="font-size:9px;color:var(--or);font-weight:700;">DRAFT</div>
            @else
              <span style="font-size:16px;color:var(--gr);">○</span>
            @endif
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endforeach

@if($students->isEmpty())
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;opacity:.4;">👥</div>
    <div style="font-size:16px;font-weight:700;color:var(--gr);">No students found for this session</div>
    <div style="font-size:13px;color:var(--bd);margin-top:6px;">Students matching school "{{ $doctorSession->school_name }}" will appear here once added by admin.</div>
  </div>
@endif
@endsection
