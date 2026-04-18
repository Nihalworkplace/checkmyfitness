@extends('layouts.app')
@section('title', $school->name)
@section('page-title', $school->name)

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Header card --}}
<div class="card mb-18">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
    <div>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div class="avatar avatar--ml avatar--dark" style="font-size:22px;">🏫</div>
        <div>
          <div style="font-family:var(--ff);font-size:22px;font-weight:900;">{{ $school->name }}</div>
          <div class="meta" style="margin-top:2px;">{{ $school->city }} · <span class="badge bb">{{ $school->board }}</span></div>
        </div>
      </div>
      @if($school->contact_person)
        <div class="meta" style="margin-top:10px;">
          Contact: <strong>{{ $school->contact_person }}</strong>
          @if($school->contact_phone) · {{ $school->contact_phone }} @endif
        </div>
      @endif
      @if($school->notes)
        <div class="fs-13" style="margin-top:8px;background:var(--lgr);border-radius:8px;padding:8px 12px;">{{ $school->notes }}</div>
      @endif
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="{{ route('admin.import.students.form', $school) }}" class="btn btn-g btn-sm">⬆ Import Students</a>
      <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-out btn-sm">Edit</a>
      <form class="d-inline" method="POST" action="{{ route('admin.schools.toggle', $school) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm {{ $school->is_active ? 'btn-or' : 'btn-g' }}">
          {{ $school->is_active ? 'Deactivate' : 'Activate' }}
        </button>
      </form>
      <a href="{{ route('admin.sessions.create') }}?school={{ $school->name }}" class="btn btn-p btn-sm">+ New Session</a>
    </div>
  </div>
</div>

{{-- Stats --}}
<div class="stat-grid mb-18">
  <div class="scard">
    <div class="sc-l">Students</div>
    <div class="sc-v">{{ $school->student_count }}</div>
    <div class="sc-s sc-s--green">Active</div>
  </div>
  <div class="scard">
    <div class="sc-l">Avg Score</div>
    @php $score = $school->avg_score; @endphp
    <div class="sc-v" style="color:{{ $score >= 72 ? 'var(--g)' : ($score >= 50 ? 'var(--or)' : 'var(--r)') }};">{{ $score ?: '—' }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Total Sessions</div>
    <div class="sc-v">{{ $sessions->count() }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Health Alerts</div>
    <div class="sc-v sc-v--red">{{ $school->alert_count }}</div>
  </div>
</div>

{{-- Session history --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Session History</div>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-g btn-sm">+ New Session</a>
  </div>
  @if($sessions->count())
    <div class="tw">
      <table>
        <thead>
          <tr>
            <th>Doctor</th><th>Session Code</th><th>Visit Date</th><th>Status</th><th>Checkups</th><th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($sessions as $sess)
            <tr>
              <td><strong>Dr. {{ $sess->doctor->name }}</strong></td>
              <td><code class="code-pill">{{ $sess->session_code }}</code></td>
              <td>{{ $sess->visit_date->format('d M Y') }}</td>
              <td>
                @php $badge = ['active'=>'bb','pending'=>'bb','expired'=>'bgr','revoked'=>'br','completed'=>'bg'][$sess->status] ?? 'bgr'; @endphp
                <span class="badge {{ $badge }}">{{ ucfirst($sess->status) }}</span>
              </td>
              <td>{{ $sess->checkups_count ?? $sess->checkups->count() }}</td>
              <td><a href="{{ route('admin.sessions.show', $sess) }}" class="btn btn-out btn-sm">View</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="empty-state">No sessions for this school yet.</div>
  @endif
</div>

@endsection
