@extends('layouts.app')
@section('title','Import Results — Doctors')
@section('page-title','Import Results')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

{{-- Summary bar --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;">
  <div class="scard">
    <div class="sc-l">Doctors Created</div>
    <div class="sc-v" style="color:var(--g);">{{ count($results['created']) }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Skipped (duplicates)</div>
    <div class="sc-v" style="color:var(--or);">{{ count($results['skipped']) }}</div>
  </div>
  <div class="scard">
    <div class="sc-l">Errors</div>
    <div class="sc-v" style="color:var(--r);">{{ count($results['errors']) }}</div>
  </div>
</div>

{{-- Created --}}
@if(count($results['created']))
<div class="card" style="margin-bottom:18px;">
  <div class="card-header"><div class="card-title" style="color:var(--g);">Created Successfully — {{ count($results['created']) }}</div></div>
  <div class="tw">
    <table>
      <thead><tr><th>Name</th><th>Staff Code</th><th>License No.</th><th>Doctor Type</th><th>Phone</th><th>School</th></tr></thead>
      <tbody>
        @foreach($results['created'] as $r)
          <tr>
            <td><strong>{{ $r['name'] }}</strong></td>
            <td><code style="font-size:11px;background:var(--lgr);padding:2px 6px;border-radius:4px;">{{ $r['staff_code'] }}</code></td>
            <td style="font-size:12px;">{{ $r['license_number'] }}</td>
            <td style="font-size:12px;">{{ $r['doctor_type'] }}</td>
            <td style="font-size:12px;">{{ $r['phone'] }}</td>
            <td style="font-size:12px;">{{ $r['school'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Skipped --}}
@if(count($results['skipped']))
<div class="card" style="margin-bottom:18px;">
  <div class="card-header"><div class="card-title" style="color:var(--or);">Skipped — {{ count($results['skipped']) }}</div></div>
  <div class="tw">
    <table>
      <thead><tr><th>Name</th><th>Staff Code</th><th>License No.</th><th>Reason</th></tr></thead>
      <tbody>
        @foreach($results['skipped'] as $r)
          <tr>
            <td>{{ $r['name'] }}</td>
            <td><code style="font-size:11px;">{{ $r['staff_code'] }}</code></td>
            <td style="font-size:12px;">{{ $r['license_number'] ?? '—' }}</td>
            <td style="font-size:12px;color:var(--or);">{{ $r['reason'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Errors --}}
@if(count($results['errors']))
<div class="card" style="margin-bottom:18px;">
  <div class="card-header"><div class="card-title" style="color:var(--r);">Errors — {{ count($results['errors']) }}</div></div>
  @foreach($results['errors'] as $err)
    <div style="display:flex;gap:8px;padding:7px 0;border-bottom:1px solid var(--lgr);font-size:12px;">
      <span style="color:var(--r);">⚠</span>
      <span>{{ $err }}</span>
    </div>
  @endforeach
</div>
@endif

{{-- Actions --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;">
  <a href="{{ route('admin.doctors') }}" class="btn btn-b">Back to Doctors</a>
  <a href="{{ route('admin.import.doctors.form') }}" class="btn btn-out">Import Another File</a>
</div>

@endsection
