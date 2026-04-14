{{-- resources/views/admin/logs.blade.php --}}
@extends('layouts.app')
@section('title','Activity Logs')
@section('page-title','Activity Logs')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
{{-- Filters --}}
<div class="card" style="margin-bottom:16px;">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div>
      <label class="form-label">Role</label>
      <select name="role" class="form-input" style="width:130px;">
        <option value="">All Roles</option>
        <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
        <option value="doctor" {{ request('role')=='doctor'?'selected':'' }}>Doctor</option>
        <option value="parent" {{ request('role')=='parent'?'selected':'' }}>Parent</option>
      </select>
    </div>
    <div>
      <label class="form-label">Action</label>
      <select name="action" class="form-input" style="width:180px;">
        <option value="">All Actions</option>
        @foreach($actions as $action)
          <option value="{{ $action }}" {{ request('action')===$action?'selected':'' }}>{{ ucwords(str_replace('_',' ',$action)) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Session</label>
      <select name="session_id" class="form-input" style="width:220px;">
        <option value="">All Sessions</option>
        @foreach($sessions as $s)
          <option value="{{ $s->id }}" {{ request('session_id')==$s->id?'selected':'' }}>{{ $s->session_code }} – Dr. {{ $s->doctor->name }}</option>
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
            <td style="color:var(--gr);font-size:11px;">{{ $log->id }}</td>
            <td style="white-space:nowrap;font-size:11px;">{{ $log->created_at->inDisplayTz()->format('d M y H:i:s') }}</td>
            <td>
              <strong style="font-size:13px;">{{ $log->user->name }}</strong>
              @if($log->user->staff_code)<br/><span style="font-size:10px;color:var(--gr);">{{ $log->user->staff_code }}</span>@endif
            </td>
            <td><span class="badge {{ ['admin'=>'bp','doctor'=>'bb','parent'=>'bg'][$log->role]??'bgr' }}">{{ $log->role }}</span></td>
            <td style="font-size:12px;font-weight:600;">{{ $log->action_label }}</td>
            <td>
              @if($log->doctorSession)
                <a href="{{ route('admin.sessions.show', $log->doctorSession) }}" style="font-size:11px;color:var(--bl);font-weight:600;">{{ $log->doctorSession->session_code }}</a>
              @else
                <span style="color:var(--gr);font-size:11px;">—</span>
              @endif
            </td>
            <td style="font-size:12px;max-width:280px;">{{ $log->description }}</td>
            <td style="font-size:11px;color:var(--gr);">{{ $log->ip_address }}</td>
          </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--gr);">No activity logs found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 0;">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
