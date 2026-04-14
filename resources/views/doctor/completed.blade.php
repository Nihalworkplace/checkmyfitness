{{-- resources/views/doctor/completed.blade.php --}}
@extends('layouts.app')
@section('title','Completed Today')
@section('page-title','Completed Today')
@section('sidebar-nav')
<a href="{{ route('doctor.session.active') }}" class="ni"><div class="ni-ico" style="background:rgba(59,130,246,0.25);">🩺</div> Checkup Session</a>
<a href="{{ route('doctor.completed') }}" class="ni active" style="background:rgba(29,158,117,0.15);color:#fff;"><div class="ni-ico" style="background:rgba(29,158,117,0.3);">✅</div> Completed Today</a>
<a href="{{ route('doctor.summary') }}" class="ni"><div class="ni-ico" style="background:rgba(245,158,11,0.2);">📊</div> Session Summary</a>
@endsection

@section('content')
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:18px;">
  <div class="scard"><div class="sc-l">Completed</div><div class="sc-v" style="color:var(--g);">{{ $checkups->count() }}</div></div>
  <div class="scard"><div class="sc-l">Total Alerts</div><div class="sc-v" style="color:var(--r);">{{ $checkups->flatMap(fn($c)=>$c->alerts??[])->count() }}</div></div>
  <div class="scard"><div class="sc-l">Avg Score</div><div class="sc-v" style="color:var(--bl);">{{ $checkups->count() ? round($checkups->avg('overall_score')) : '—' }}</div></div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Completed Checkups — {{ now()->inDisplayTz()->format('d M Y') }}</div></div>
  <div class="tw">
    <table>
      <thead>
        <tr><th>Student</th><th>Class</th><th>Age</th><th>Score</th><th>Key Alerts</th><th>Notes</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($checkups as $c)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:8px;background:{{ $c->student->gender==='M'?'#3B82F6':'#8B5CF6' }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;">{{ strtoupper(substr($c->student->name,0,1)) }}</div>
                <strong>{{ $c->student->name }}</strong>
              </div>
            </td>
            <td>{{ $c->student->class_section }}</td>
            <td>{{ $c->student->age }}</td>
            <td>
              @if($c->overall_score)
                <span style="font-weight:800;font-size:15px;color:{{ $c->overall_score>=75?'var(--g)':($c->overall_score>=55?'var(--or)':'var(--r)') }};">{{ $c->overall_score }}</span>
              @else
                —
              @endif
            </td>
            <td style="max-width:200px;">
              @if(count($c->alerts??[])>0)
                @foreach($c->alerts as $a)
                  <span class="badge br" style="display:block;margin-bottom:2px;font-size:9px;">{{ Str::limit($a,40) }}</span>
                @endforeach
              @else
                <span class="badge bg">None</span>
              @endif
            </td>
            <td style="font-size:12px;max-width:180px;color:var(--gr);">{{ Str::limit($c->doctor_notes??'—',60) }}</td>
            <td>
              <a href="{{ route('doctor.checkup.form', $c->student) }}" class="btn btn-out btn-sm">Edit</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="text-align:center;color:var(--gr);padding:24px;">No checkups completed yet today.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
