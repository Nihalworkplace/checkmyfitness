@extends('layouts.app')
@section('title','Activity Logs')
@section('page-title','Activity Logs')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Filters --}}
<div class="card mb-16">
  <form method="GET" class="filter-bar">
    <div>
      <label class="form-label">Role</label>
      <select name="role" class="form-input" style="width:130px;">
        <option value="">All Roles</option>
        <option value="admin"  {{ request('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
        <option value="doctor" {{ request('role') === 'doctor' ? 'selected' : '' }}>Doctor</option>
        <option value="parent" {{ request('role') === 'parent' ? 'selected' : '' }}>Parent</option>
      </select>
    </div>
    <div>
      <label class="form-label">Action</label>
      <select name="action" class="form-input" style="width:180px;">
        <option value="">All Actions</option>
        @foreach($actions as $action)
          <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Session</label>
      <select name="session_id" class="form-input" style="width:220px;">
        <option value="">All Sessions</option>
        @foreach($sessions as $s)
          <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->session_code }} – Dr. {{ $s->doctor->name }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="btn btn-dk">Filter</button>
    <a href="{{ route('admin.logs') }}" class="btn btn-out">Clear</a>
  </form>
</div>

<div class="card">
  <div class="tw">
    <table>
      <thead>
        <tr><th>#</th><th>Time</th><th>User</th><th>Role</th><th>Action</th><th>Session</th><th>Description</th><th>IP</th></tr>
      </thead>
      <tbody>
        @forelse($logs as $log)
          <tr>
            <td class="meta">{{ $log->id }}</td>
            <td class="meta" style="white-space:nowrap;">{{ $log->created_at->inDisplayTz()->format('d M y H:i:s') }}</td>
            <td>
              <strong class="fs-13">{{ $log->user->name }}</strong>
              @if($log->user->staff_code)<br /><span class="meta">{{ $log->user->staff_code }}</span>@endif
            </td>
            <td><span class="badge {{ ['admin'=>'bp','doctor'=>'bb','parent'=>'bg'][$log->role] ?? 'bgr' }}">{{ $log->role }}</span></td>
            <td class="fs-12 fw-600">{{ $log->action_label }}</td>
            <td>
              @if($log->doctorSession)
                <a href="{{ route('admin.sessions.show', $log->doctorSession) }}" class="meta text-blue fw-600">{{ $log->doctorSession->session_code }}</a>
              @else
                <span class="meta">—</span>
              @endif
            </td>
            <td class="fs-12" style="max-width:280px;">{{ $log->description }}</td>
            <td class="meta">{{ $log->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="8" class="empty-state">No activity logs found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination-wrap">{{ $logs->withQueryString()->links() }}</div>
</div>

@endsection
