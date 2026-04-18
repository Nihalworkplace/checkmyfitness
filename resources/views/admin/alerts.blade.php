@extends('layouts.app')
@section('title','Health Alerts')
@section('page-title','Health Alerts')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Stats --}}
<div class="stat-grid stat-grid-3 mb-18">
  <div class="scard">
    <div class="sc-l">Total Alerts</div>
    <div class="sc-v sc-v--red">{{ $totalAlerts }}</div>
    <div class="sc-s sc-s--muted">Across all schools</div>
  </div>
  <div class="scard">
    <div class="sc-l">Critical Alerts</div>
    <div class="sc-v sc-v--red">{{ $criticalAlerts }}</div>
    <div class="sc-s sc-s--red">Need immediate attention</div>
  </div>
  <div class="scard">
    <div class="sc-l">Students Affected</div>
    <div class="sc-v sc-v--orange">{{ $studentsAffected }}</div>
    <div class="sc-s sc-s--muted">With 1+ alerts</div>
  </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar">
  <input name="search" class="form-input" style="max-width:220px;" placeholder="🔍 Student name…" value="{{ request('search') }}" />
  <select name="school" class="form-input" style="width:200px;">
    <option value="">All Schools</option>
    @foreach($schools as $school)
      <option value="{{ $school }}" {{ request('school') === $school ? 'selected' : '' }}>{{ $school }}</option>
    @endforeach
  </select>
  <select name="critical" class="form-input" style="width:160px;">
    <option value="">All Alerts</option>
    <option value="1" {{ request('critical') === '1' ? 'selected' : '' }}>Critical Only</option>
  </select>
  <button type="submit" class="btn btn-dk btn-sm">Filter</button>
  @if(request()->hasAny(['search','school','critical']))
    <a href="{{ route('admin.alerts') }}" class="btn btn-out btn-sm">Clear</a>
  @endif
</form>

<div class="card">
  <div class="card-header">
    <div class="card-title">⚠️ Student Health Alerts ({{ $checkups->total() }} checkups with alerts)</div>
  </div>
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>Student</th><th>School</th><th>Class</th><th>Overall Score</th><th>Alerts</th><th>Checkup Date</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($checkups as $checkup)
          <tr>
            <td>
              <div class="fw-700">{{ $checkup->student->name }}</div>
              <div class="meta">{{ $checkup->student->gender === 'M' ? 'Boy' : 'Girl' }} · Age {{ $checkup->student->age }}</div>
            </td>
            <td class="fs-13">{{ $checkup->student->school_name }}</td>
            <td>{{ $checkup->student->class_section }}</td>
            <td>
              @if($checkup->overall_score)
                <span class="fw-700" style="font-size:14px;color:{{ $checkup->overall_score>=72?'var(--g)':($checkup->overall_score>=50?'var(--or)':'var(--r)') }};">
                  {{ $checkup->overall_score }}
                </span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @foreach($checkup->alerts ?? [] as $alert)
                <div class="badge {{ str_contains($alert,'CRITICAL') ? 'br' : 'by' }}" style="margin-bottom:3px;display:block;max-width:300px;white-space:normal;line-height:1.4;">
                  {{ str_contains($alert,'CRITICAL') ? '🔴 ' : '⚠️ ' }}{{ $alert }}
                </div>
              @endforeach
            </td>
            <td class="meta">{{ $checkup->checkup_date->format('d M Y') }}</td>
            <td>
              @if($checkup->doctorSession)
                <a href="{{ route('admin.sessions.show', $checkup->doctorSession) }}" class="btn btn-out btn-sm">View Session</a>
              @else
                <span class="meta">No session</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="empty-state--lg empty-state">No health alerts found. 🎉</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($checkups->hasPages())
    <div class="pagination-wrap">{{ $checkups->links() }}</div>
  @endif
</div>

@endsection
