{{-- resources/views/admin/sessions/index.blade.php --}}
@extends('layouts.app')
@section('title','Sessions')
@section('page-title','Session Manager')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <select name="status" class="form-input" style="width:140px;" onchange="this.form.submit()">
      <option value="">All Statuses</option>
      @foreach(['pending','active','expired','revoked','completed'] as $s)
        <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <select name="doctor_id" class="form-input" style="width:200px;" onchange="this.form.submit()">
      <option value="">All Doctors</option>
      @foreach($doctors as $d)
        <option value="{{ $d->id }}" {{ request('doctor_id')==$d->id?'selected':'' }}>Dr. {{ $d->name }}</option>
      @endforeach
    </select>
    <input type="text" name="search" class="form-input" placeholder="Search code or school…" value="{{ request('search') }}" style="width:200px;"/>
    <button type="submit" class="btn btn-dk">Filter</button>
    <a href="{{ route('admin.sessions.index') }}" class="btn btn-out">Clear</a>
  </form>
  <a href="{{ route('admin.sessions.create') }}" class="btn btn-p">+ New Session</a>
</div>

<div class="card">
  <div class="tw">
    <table>
      <thead><tr><th>Session Code</th><th>Doctor</th><th>School</th><th>Date</th><th>Expires</th><th>Status</th><th>Checkups</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($sessions as $sess)
          <tr>
            <td>
              <code style="font-size:11px;background:var(--lgr);padding:2px 7px;border-radius:5px;">{{ $sess->session_code }}</code>
              @if($sess->is_reopened)<br/><span class="badge by" style="font-size:9px;margin-top:3px;">Reopened</span>@endif
            </td>
            <td>Dr. {{ $sess->doctor?->name ?? 'Unknown' }}<br/><span style="font-size:11px;color:var(--gr);">{{ $sess->doctor?->staff_code }}</span></td>
            <td>{{ $sess->school_name }}</td>
            <td>{{ ($sess->starts_at ?? $sess->created_at)->inDisplayTz()->format('d M y H:i') }}</td>
            <td style="white-space:nowrap;font-size:12px;">{{ $sess->expires_at->inDisplayTz()->format('d M y H:i') }}</td>
            <td>
              @php $b=['active'=>'bb','pending'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$sess->status_badge]??'bgr'; @endphp
              <span class="badge {{ $b }}">{{ ucfirst($sess->status_badge) }}</span>
            </td>
            <td>{{ $sess->checkups->count() }}</td>
            <td>
              <a href="{{ route('admin.sessions.show', $sess) }}" class="btn btn-out btn-sm">View</a>
              @if(in_array($sess->status,['active','pending']))
                <form method="POST" action="{{ route('admin.sessions.revoke', $sess) }}" style="display:inline;" onsubmit="return confirm('Revoke?')">@csrf<button type="submit" class="btn btn-r btn-sm">Revoke</button></form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--gr);">No sessions found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 0;">{{ $sessions->withQueryString()->links() }}</div>
</div>
@endsection
