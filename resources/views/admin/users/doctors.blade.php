{{-- resources/views/admin/users/doctors.blade.php --}}
@extends('layouts.app')
@section('title','Doctors')
@section('page-title','Manage Doctors')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <input type="text" name="search" class="form-input" placeholder="Search name or staff code…" value="{{ request('search') }}" style="width:220px;"/>
    <select name="status" class="form-input" style="width:130px;" onchange="this.form.submit()">
      <option value="">All Status</option>
      <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
      <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
    </select>
    <button type="submit" class="btn btn-dk">Search</button>
  </form>
  <div style="display:flex;gap:8px;">
    <a href="{{ route('admin.import.doctors.form') }}" class="btn btn-out">⬆ Import CSV</a>
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-b">+ Add Doctor</a>
  </div>
</div>

<div class="card">
  <div class="tw">
    <table>
      <thead><tr><th>Doctor</th><th>Staff Code</th><th>Specialisation</th><th>License No.</th><th>Phone</th><th>Sessions</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($doctors as $d)
          @php
            $typeColors = [
              'general_physician' => '#3B82F6',
              'dentist'           => '#8B5CF6',
              'eye_specialist'    => '#06B6D4',
              'audiologist_ent'   => '#F59E0B',
              'physiotherapist'   => '#10B981',
              'psychologist'      => '#EC4899',
              'lab_technician'    => '#EF4444',
            ];
            $typeColor = $typeColors[$d->doctor_type] ?? '#6B7280';
            $typeLabel = \App\Models\User::DOCTOR_TYPES[$d->doctor_type] ?? '—';
          @endphp
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;background:{{ $typeColor }};border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;">{{ strtoupper(substr($d->name,0,1)) }}</div>
                <strong>Dr. {{ $d->name }}</strong>
              </div>
            </td>
            <td><code style="font-size:11px;background:var(--lgr);padding:2px 7px;border-radius:5px;">{{ $d->staff_code }}</code></td>
            <td>
              @if($d->doctor_type)
                <span style="display:inline-block;font-size:10px;font-weight:700;background:{{ $typeColor }}18;color:{{ $typeColor }};padding:3px 8px;border-radius:20px;border:1px solid {{ $typeColor }}44;">{{ $typeLabel }}</span>
              @else
                <span class="text-muted" style="font-size:12px;">—</span>
              @endif
            </td>
            <td style="font-size:12px;">{{ $d->license_number ?? '—' }}</td>
            <td>{{ $d->phone ?? '—' }}</td>
            <td>{{ $d->doctor_sessions_count }}</td>
            <td><span class="badge {{ $d->is_active?'bg':'br' }}">{{ $d->is_active?'Active':'Inactive' }}</span></td>
            <td>
              <a href="{{ route('admin.sessions.create') }}?doctor_id={{ $d->id }}" class="btn btn-b btn-sm">New Session</a>
              <form method="POST" action="{{ route('admin.doctors.toggle', $d) }}" style="display:inline;" onsubmit="return confirm('Toggle status?')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-{{ $d->is_active?'r':'g' }} btn-sm">{{ $d->is_active?'Deactivate':'Activate' }}</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--gr);">No doctors yet. <a href="{{ route('admin.doctors.create') }}" style="color:var(--bl);">Add one →</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:16px 0;">{{ $doctors->withQueryString()->links() }}</div>
</div>
@endsection
