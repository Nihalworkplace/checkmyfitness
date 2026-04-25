@php
    $navGuardian  = Auth::guard('parent')->user();
    $navStudents  = $navGuardian ? $navGuardian->students()->get() : collect();
    $navStudentId = optional(request()->route('student'))->id;
@endphp

@if($navStudents->isNotEmpty())
  @foreach($navStudents as $stu)
    <div class="nb-label">{{ $stu->name }}</div>
    <a href="{{ route('parent.dashboard') }}"
       class="ni {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
      <div class="ni-ico" style="background:rgba(29,158,117,0.2);">🏠</div> Overview
    </a>
    <a href="{{ route('parent.report', $stu) }}"
       class="ni {{ request()->routeIs('parent.report') && $navStudentId == $stu->id ? 'active' : '' }}">
      <div class="ni-ico" style="background:rgba(29,158,117,0.2);">📋</div> Health Report
    </a>
    <a href="{{ route('parent.timeline', $stu) }}"
       class="ni {{ request()->routeIs('parent.timeline') && $navStudentId == $stu->id ? 'active' : '' }}">
      <div class="ni-ico" style="background:rgba(59,130,246,0.2);">📈</div> Timeline
    </a>
  @endforeach
@endif

<div class="nb-label" style="margin-top:12px;">Community</div>
<a href="{{ route('parent.community.index') }}"
   class="ni {{ request()->routeIs('parent.community.index') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(29,158,117,0.2);">📣</div> Community Feed
</a>
