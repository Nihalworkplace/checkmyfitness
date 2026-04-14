@extends('layouts.app')
@section('title','Partner Schools')
@section('page-title','Partner Schools')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')

{{-- Filters --}}
<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:flex-end;">
  <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap;">
    <input name="search" class="form-input" style="max-width:240px;" placeholder="🔍 Search school or city…" value="{{ request('search') }}"/>
    <select name="board" class="form-input" style="width:140px;">
      <option value="">All Boards</option>
      @foreach(['CBSE','ICSE','GSEB','IB','IGCSE','State','Other'] as $b)
        <option value="{{ $b }}" {{ request('board')===$b?'selected':'' }}>{{ $b }}</option>
      @endforeach
    </select>
    <select name="status" class="form-input" style="width:140px;">
      <option value="">All Status</option>
      <option value="active"   {{ request('status')==='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="btn btn-dk btn-sm">Filter</button>
    @if(request()->hasAny(['search','board','status']))
      <a href="{{ route('admin.schools.index') }}" class="btn btn-out btn-sm">Clear</a>
    @endif
  </form>
  <a href="{{ route('admin.schools.create') }}" class="btn btn-g">+ Add School</a>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">🏫 Partner Schools ({{ $schools->total() }})</div>
  </div>
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>School</th>
          <th>City</th>
          <th>Board</th>
          <th>Students</th>
          <th>Avg Score</th>
          <th>Last Checkup</th>
          <th>Alerts</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($schools as $school)
          <tr>
            <td>
              <div style="font-weight:700;font-size:13px;">{{ $school->name }}</div>
              @if($school->contact_person)
                <div style="font-size:11px;color:var(--gr);">{{ $school->contact_person }}</div>
              @endif
            </td>
            <td>{{ $school->city }}</td>
            <td><span class="badge bb">{{ $school->board }}</span></td>
            <td style="font-weight:600;">{{ $school->student_count }}</td>
            <td>
              @php $score = $school->avg_score; @endphp
              @if($score > 0)
                <span style="font-weight:700;color:{{ $score>=72?'var(--g)':($score>=50?'var(--or)':'var(--r)') }};">{{ $score }}</span>
              @else
                <span style="color:var(--gr);">—</span>
              @endif
            </td>
            <td>{{ $school->last_session_date ?? '—' }}</td>
            <td>
              @php $alerts = $school->alert_count; @endphp
              @if($alerts > 0)
                <span class="badge {{ $alerts > 30 ? 'br' : 'by' }}">{{ $alerts }}</span>
              @else
                <span class="badge bg">Clear</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $school->is_active ? 'bg' : 'bgr' }}">
                {{ $school->is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-out btn-sm">View</a>
                <a href="{{ route('admin.schools.edit', $school) }}" class="btn btn-out btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.schools.toggle', $school) }}" style="display:inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="btn btn-sm {{ $school->is_active ? 'btn-or' : 'btn-g' }}">
                    {{ $school->is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" style="text-align:center;color:var(--gr);padding:40px;">
              No schools found.
              <a href="{{ route('admin.schools.create') }}" style="color:var(--g);">Add the first school →</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($schools->hasPages())
    <div style="padding:16px 0 4px;">{{ $schools->links() }}</div>
  @endif
</div>
@endsection
