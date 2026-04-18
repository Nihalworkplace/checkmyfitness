@extends('layouts.app')
@section('title','Parents')
@section('page-title','Manage Parents')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <input type="text" name="search" class="form-input" placeholder="Search name or email…" value="{{ request('search') }}" style="width:240px;"/>
    <button type="submit" class="btn btn-dk btn-sm">Search</button>
    @if(request('search'))
      <a href="{{ route('admin.parents') }}" class="btn btn-out btn-sm">Clear</a>
    @endif
  </form>
  <a href="{{ route('admin.parents.create') }}" class="btn btn-g">+ Add Parent</a>
</div>

<div class="card">
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>Parent</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Students</th>
          <th>Ref Code</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($parents as $p)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;background:#1D9E75;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;">
                  {{ strtoupper(substr($p->name,0,1)) }}
                </div>
                <strong>{{ $p->name }}</strong>
              </div>
            </td>
            <td>{{ $p->email ?? '—' }}</td>
            <td>{{ $p->phone ?? '—' }}</td>
            <td>
              <span class="badge bb">{{ $p->students_count }} student{{ $p->students_count != 1 ? 's' : '' }}</span>
            </td>
            <td>
              @if($p->reference_code)
                <code style="font-size:11px;background:var(--lgr);padding:2px 7px;border-radius:5px;">{{ $p->reference_code }}</code>
              @else
                <span style="color:var(--gr);">—</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $p->is_active ? 'bg' : 'br' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span>
            </td>
            <td><a href="{{ route('admin.parents.show', $p) }}" class="btn btn-out btn-sm">View</a></td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:32px;color:var(--gr);">
              No parents yet. <a href="{{ route('admin.parents.create') }}" style="color:var(--g);">Add the first parent →</a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($parents->hasPages())
    <div style="padding:16px 0 4px;">{{ $parents->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
