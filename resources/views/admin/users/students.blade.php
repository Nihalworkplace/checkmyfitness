{{-- resources/views/admin/users/students.blade.php --}}
@extends('layouts.app')
@section('title','Students')
@section('page-title','All Students')
@section('sidebar-nav')@include('admin.partials.nav')@endsection
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <input type="text" name="search" class="form-input" placeholder="Search name, ref code, school…" value="{{ request('search') }}" style="width:260px;"/>
    <button type="submit" class="btn btn-dk">Search</button>
    <a href="{{ route('admin.students') }}" class="btn btn-out">Clear</a>
  </form>
  <a href="{{ route('admin.students.create') }}" class="btn btn-g">+ Add Student</a>
</div>
<div class="card">
  <div class="tw">
    <table>
      <thead><tr><th>Student</th><th>Reference Code</th><th>School</th><th>Class</th><th>Parent</th><th>Age</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($students as $s)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;background:{{ $s->gender==='M'?'#3B82F6':'#8B5CF6' }};border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;">{{ strtoupper(substr($s->name,0,1)) }}</div>
                <strong>{{ $s->name }}</strong>
              </div>
            </td>
            <td><code style="font-size:11px;background:var(--lgr);padding:2px 7px;border-radius:5px;">{{ $s->reference_code }}</code></td>
            <td>{{ $s->school_name }}</td>
            <td>{{ $s->class_section }}</td>
            <td>{{ $s->parent->name }}</td>
            <td>{{ $s->age }}</td>
            <td><span class="badge {{ $s->is_active?'bg':'br' }}">{{ $s->is_active?'Active':'Inactive' }}</span></td>
          </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--gr);">No students yet. <a href="{{ route('admin.students.create') }}" style="color:var(--g);">Add one →</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 0;">{{ $students->withQueryString()->links() }}</div>
</div>
@endsection
