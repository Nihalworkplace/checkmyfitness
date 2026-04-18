@extends('layouts.app')
@section('title','Import Results — '.$school->name)
@section('page-title','Import Results')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

{{-- Summary bar --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;">
  <div class="scard">
    <div class="sc-l">Students Created</div>
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

{{-- Default password notice (shown once if any new parent accounts were created) --}}
@php $newParentCount = collect($results['created'])->filter(fn($r) => $r['temp_password'] !== null)->count(); @endphp
@if($newParentCount)
  <div class="card" style="margin-bottom:18px;background:#FFFBEB;border:1.5px solid #FCD34D;">
    <div style="display:flex;gap:14px;align-items:flex-start;">
      <div style="font-size:28px;flex-shrink:0;">🔑</div>
      <div>
        <div style="font-size:14px;font-weight:700;color:#92400E;margin-bottom:6px;">
          {{ $newParentCount }} new parent account{{ $newParentCount > 1 ? 's' : '' }} created
        </div>
        <div style="font-size:13px;color:#78350F;line-height:1.6;">
          All new parents have been assigned the <strong>system default password</strong>:
          <code style="background:#FEF9C3;padding:2px 10px;border-radius:6px;font-size:14px;font-weight:800;color:#92400E;margin:0 4px;">{{ config('app.parent_default_password') }}</code>
          <br/>Parents can log in with their email + this password, or use their child's reference code + date of birth.
          <br/><span style="font-size:11px;color:#A16207;">To change the default password, update <code>PARENT_DEFAULT_PASSWORD</code> in your <code>.env</code> file.</span>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- Created --}}
@if(count($results['created']))
<div class="card" style="margin-bottom:18px;">
  <div class="card-header">
    <div class="card-title" style="color:var(--g);">Created Successfully — {{ count($results['created']) }}</div>
  </div>
  <div class="tw">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Class</th>
          <th>Ref Code</th>
          <th>Parent</th>
          <th>Parent Email</th>
          <th>Account</th>
        </tr>
      </thead>
      <tbody>
        @foreach($results['created'] as $r)
          <tr>
            <td><strong>{{ $r['student'] }}</strong></td>
            <td>{{ $r['class'] }}</td>
            <td><code style="font-size:11px;background:var(--lgr);padding:2px 6px;border-radius:4px;">{{ $r['ref'] }}</code></td>
            <td>{{ $r['parent_name'] }}</td>
            <td style="font-size:12px;">{{ $r['parent_email'] }}</td>
            <td>
              @if($r['temp_password'])
                <span class="badge bg" style="font-size:10px;">New</span>
              @else
                <span class="badge by" style="font-size:10px;">Existing</span>
              @endif
            </td>
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
      <thead><tr><th>Student</th><th>Class</th><th>Reason</th></tr></thead>
      <tbody>
        @foreach($results['skipped'] as $r)
          <tr>
            <td>{{ $r['student'] }}</td>
            <td>{{ $r['class'] }}</td>
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
  <div style="margin-top:12px;font-size:12px;color:var(--gr);">Fix the rows with errors in your Excel file and re-import only those rows.</div>
</div>
@endif

{{-- Actions --}}
<div style="display:flex;gap:10px;flex-wrap:wrap;">
  <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-g">Back to {{ $school->name }}</a>
  <a href="{{ route('admin.import.students.form', $school) }}" class="btn btn-out">Import Another File</a>
  <a href="{{ route('admin.students') }}" class="btn btn-out">View All Students</a>
</div>

@endsection
