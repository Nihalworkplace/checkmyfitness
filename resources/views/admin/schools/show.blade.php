@extends('layouts.app')
@section('title', $school->name)
@section('page-title', $school->name)

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Header card --}}
<div class="card" style="margin-bottom:18px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
    <div>
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="width:48px;height:48px;background:var(--dk);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;">🏫</div>
        <div>
          <div style="font-family:var(--ff);font-size:22px;font-weight:900;">{{ $school->name }}</div>
          <div style="font-size:13px;color:var(--gr);margin-top:2px;">{{ $school->city }} · <span class="badge bb">{{ $school->board }}</span></div>
        </div>
      </div>
      @if($school->contact_person)
        <div style="margin-top:10px;font-size:12px;color:var(--gr);">
          Contact: <strong>{{ $school->contact_person }}</strong>
          @if($school->contact_phone) · {{ $school->contact_phone }} @endif
        </div>
      @endif
      @if($school->notes)
        <div style="margin-top:8px;font-size:13px;color:var(--dk);background:var(--lgr);border-radius:8px;padding:8px 12px;">{{ $school->notes }}</div>
      @endif
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-out btn-sm">Edit</a>
      <form method="POST" action="{{ route('admin.schools.toggle', $school) }}" style="display:inline">
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
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:18px;">
  <div class="scard">
    <div class="sc-l">Students</div>
    <div class="sc-v">{{ $school->student_count }}</div>
    <div class="sc-s" style="color:var(--g);">Active</div>
  </div>
  <div class="scard">
    <div class="sc-l">Avg Score</div>
    @php $score = $school->avg_score; @endphp
    <div class="sc-v" style="color:{{ $score>=72?'var(--g)':($score>=50?'var(--or)':'var(--r)') }};">{{ $score ?: '—' }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Total Sessions</div>
    <div class="sc-v">{{ $sessions->count() }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Health Alerts</div>
    <div class="sc-v" style="color:var(--r);">{{ $school->alert_count }}</div>
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
              <td><code style="font-size:11px;background:var(--lgr);padding:2px 7px;border-radius:5px;">{{ $sess->session_code }}</code></td>
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
    <div style="text-align:center;padding:32px;color:var(--gr);">No sessions for this school yet.</div>
  @endif
</div>
@endsection
