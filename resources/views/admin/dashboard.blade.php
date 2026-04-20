@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Stat cards --}}
<div class="stat-grid">
  <div class="scard"><div class="sc-l">Partner Schools</div><div class="sc-v">{{ $stats['total_schools'] }}</div></div>
  <div class="scard"><div class="sc-l">Active Doctors</div><div class="sc-v sc-v--blue">{{ $stats['total_doctors'] }}</div></div>
  <div class="scard"><div class="sc-l">Registered Parents</div><div class="sc-v sc-v--green">{{ $stats['total_parents'] }}</div></div>
  <div class="scard"><div class="sc-l">Total Students</div><div class="sc-v">{{ $stats['total_students'] }}</div></div>
  <div class="scard">
    <div class="sc-l">Active Sessions</div>
    <div class="sc-v sc-v--blue">{{ $stats['active_sessions'] }}</div>
    <div class="sc-s sc-s--orange">{{ $stats['pending_sessions'] }} pending</div>
  </div>
  <div class="scard"><div class="sc-l">Total Checkups</div><div class="sc-v sc-v--green">{{ $stats['total_checkups'] }}</div></div>
  <div class="scard"><div class="sc-l">Health Alerts</div><div class="sc-v sc-v--red">{{ $stats['total_alerts'] }}</div></div>
  <div class="scard">
    <div class="sc-l">Quick Actions</div>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-b btn-sm btn-full mt-8">+ New Session</a>
  </div>
</div>

<div class="g2">
  {{-- Active sessions --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">🟢 Active Doctor Sessions</div>
      <a href="{{ route('admin.sessions.index', ['status'=>'active']) }}" class="btn btn-sm btn-out">View All</a>
    </div>
    @forelse($activeSessions as $sess)
      <div class="list-row">
        <div class="avatar avatar--md avatar--blue">
          {{ strtoupper(substr($sess->doctor?->name ?? '?', 0, 1)) }}
        </div>
        <div class="flex-auto">
          <div class="list-row__title">Dr. {{ $sess->doctor?->name ?? 'Unknown' }}</div>
          <div class="list-row__sub">{{ $sess->school_name }} · Exp: {{ $sess->expires_at->inDisplayTz()->format('H:i') }}</div>
        </div>
        <div class="list-row__right">
          <span class="badge bb">Active</span><br />
          <span class="meta">{{ $sess->checkups->count() }} checkups</span>
        </div>
        <form method="POST" action="{{ route('admin.sessions.revoke', $sess) }}" onsubmit="return confirm('Revoke this session? Doctor will be logged out immediately.')">
          @csrf
          <button type="submit" class="btn btn-r btn-sm">Revoke</button>
        </form>
      </div>
    @empty
      <div class="empty-state">No active sessions right now.</div>
    @endforelse
  </div>

  {{-- Recent activity logs --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">📋 Recent Activity</div>
      <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-out">View All</a>
    </div>
    @foreach($recentLogs->take(12) as $log)
      <div class="list-row fs-12">
        <div class="flex-auto">
          <span class="fw-600">{{ $log->actor?->name ?? 'Unknown' }}</span>
          <span class="text-muted"> — {{ $log->action_label }}</span>
          @if($log->doctorSession)
            <span class="badge bb" style="font-size:9px;margin-left:4px;">{{ $log->doctorSession->session_code }}</span>
          @endif
        </div>
        <div class="meta" style="flex-shrink:0;">{{ $log->created_at->diffForHumans() }}</div>
      </div>
    @endforeach
  </div>
</div>

{{-- School performance overview --}}
@if($schoolPerformance->count())
<div class="card mb-18">
  <div class="card-header">
    <div class="card-title">🏫 School Performance Overview</div>
    <a href="{{ route('admin.schools.index') }}" class="btn btn-out btn-sm">View All Schools →</a>
  </div>
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>School</th><th>City</th><th>Students</th><th>Avg Score</th><th>Last Checkup</th><th>Alerts</th><th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($schoolPerformance as $school)
          @php $score = $school->avg_score; @endphp
          <tr>
            <td><strong>{{ $school->name }}</strong></td>
            <td>{{ $school->city }}</td>
            <td>{{ $school->student_count }}</td>
            <td>
              @if($score)
                <span class="fw-700" style="color:{{ $score>=72?'var(--g)':($score>=50?'var(--or)':'var(--r)') }};">{{ $score }}</span>
              @else —
              @endif
            </td>
            <td>{{ $school->last_session_date ?? '—' }}</td>
            <td>
              @php $alertCount = $school->alert_count; @endphp
              <span class="badge {{ $alertCount > 0 ? ($alertCount > 30 ? 'br' : 'by') : 'bg' }}">
                {{ $alertCount > 0 ? $alertCount : 'Clear' }}
              </span>
            </td>
            <td><a href="{{ route('admin.schools.show', $school) }}" class="btn btn-out btn-sm">View</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Recent sessions --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Recent Sessions</div>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-g btn-sm">+ New Session</a>
  </div>
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>Doctor</th><th>School</th><th>Session Code</th><th>Visit Date</th><th>Expires</th><th>Status</th><th>Checkups</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentSessions as $sess)
          <tr>
            <td>
              <strong>Dr. {{ $sess->doctor?->name ?? 'Unknown' }}</strong><br />
              <span class="meta">{{ $sess->doctor?->staff_code }}</span>
            </td>
            <td>{{ $sess->school_name }}</td>
            <td>
              <code class="code-pill">{{ $sess->session_code }}</code>
              @if($sess->is_reopened)<span class="badge by" style="margin-left:4px;">Reopened</span>@endif
            </td>
            <td>{{ $sess->visit_date->format('d M Y') }}</td>
            <td>{{ $sess->expires_at->inDisplayTz()->format('d M H:i') }}</td>
            <td>
              @php $badge = ['active'=>'bb','pending'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$sess->status_badge] ?? 'bgr'; @endphp
              <span class="badge {{ $badge }}">{{ ucfirst($sess->status_badge) }}</span>
            </td>
            <td>{{ $sess->checkups_count ?? 0 }}</td>
            <td>
              <a href="{{ route('admin.sessions.show', $sess) }}" class="btn btn-out btn-sm">View</a>
              @if(in_array($sess->status, ['expired','revoked','completed']))
                <button onclick="document.getElementById('reopen-{{ $sess->id }}').style.display='block'" class="btn btn-or btn-sm">Reopen</button>
              @elseif(in_array($sess->status, ['active','pending']))
                <form class="d-inline" method="POST" action="{{ route('admin.sessions.revoke', $sess) }}" onsubmit="return confirm('Revoke session?')">
                  @csrf
                  <button type="submit" class="btn btn-r btn-sm">Revoke</button>
                </form>
              @endif
            </td>
          </tr>
          {{-- Inline reopen form --}}
          <tr id="reopen-{{ $sess->id }}" class="hidden" style="background:#FFFBEB;">
            <td colspan="8">
              <form method="POST" action="{{ route('admin.sessions.reopen', $sess) }}" class="filter-bar" style="padding:8px 0;">
                @csrf
                <div>
                  <label class="form-label">New Visit Date <span class="req">*</span></label>
                  <input type="date" name="visit_date" class="form-input" style="width:160px;" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="flex-auto" style="min-width:200px;">
                  <label class="form-label">Admin Notes</label>
                  <input type="text" name="admin_notes" class="form-input" placeholder="Reason for reopening…" />
                </div>
                <button type="submit" class="btn btn-or">Generate New Session Code</button>
                <button type="button" onclick="document.getElementById('reopen-{{ $sess->id }}').style.display='none'" class="btn btn-out">Cancel</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="empty-state">
              No sessions yet. <a href="{{ route('admin.sessions.create') }}" class="text-green">Create one →</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
