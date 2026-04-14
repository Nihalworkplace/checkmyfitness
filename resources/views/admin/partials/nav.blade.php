{{-- resources/views/admin/partials/nav.blade.php --}}
<div class="nb-label">Main</div>
<a href="{{ route('admin.dashboard') }}" class="ni {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(139,92,246,0.2);">📊</div> Dashboard
</a>

<div class="nb-label">Schools & Users</div>
<a href="{{ route('admin.schools.index') }}" class="ni {{ request()->routeIs('admin.schools*') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(29,158,117,0.18);">🏫</div> Schools
</a>
<a href="{{ route('admin.students') }}" class="ni {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(245,158,11,0.18);">🎒</div> Students
</a>
<a href="{{ route('admin.doctors') }}" class="ni {{ request()->routeIs('admin.doctors*') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(59,130,246,0.18);">🩺</div> Doctors
</a>
<a href="{{ route('admin.parents') }}" class="ni {{ request()->routeIs('admin.parents*') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(139,92,246,0.18);">👪</div> Parents
</a>

<div class="nb-label">Sessions</div>
<a href="{{ route('admin.sessions.index') }}" class="ni {{ request()->routeIs('admin.sessions.index') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(59,130,246,0.18);">🔑</div> All Sessions
</a>
<a href="{{ route('admin.sessions.create') }}" class="ni {{ request()->routeIs('admin.sessions.create') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(29,158,117,0.18);">➕</div> New Session
</a>

<div class="nb-label">Reports</div>
<a href="{{ route('admin.alerts') }}" class="ni {{ request()->routeIs('admin.alerts') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(239,68,68,0.18);">⚠️</div> Health Alerts
</a>
<a href="{{ route('admin.community.index') }}" class="ni {{ request()->routeIs('admin.community*') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(245,158,11,0.18);">📣</div> Community
</a>
<a href="{{ route('admin.logs') }}" class="ni {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
  <div class="ni-ico" style="background:rgba(100,116,139,0.18);">📋</div> Activity Logs
</a>
