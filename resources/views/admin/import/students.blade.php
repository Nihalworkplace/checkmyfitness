@extends('layouts.app')
@section('title','Import Students — '.$school->name)
@section('page-title','Import Students')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

<div style="max-width:680px;">

  {{-- Breadcrumb --}}
  <div style="font-size:12px;color:var(--gr);margin-bottom:18px;">
    <a href="{{ route('admin.schools.index') }}" style="color:var(--bl);">Schools</a>
    → <a href="{{ route('admin.schools.show', $school) }}" style="color:var(--bl);">{{ $school->name }}</a>
    → Import Students
  </div>

  {{-- Header --}}
  <div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;">
    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:1.5px;margin-bottom:6px;">BULK IMPORT</div>
    <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:#fff;margin-bottom:4px;">Import Students &amp; Parents</div>
    <div style="font-size:13px;color:rgba(255,255,255,0.45);">{{ $school->name }} · {{ $school->city }}</div>
  </div>

  {{-- How it works --}}
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header"><div class="card-title">How It Works</div></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;padding-top:4px;">
      @foreach([
        ['1', 'Download the sample CSV file below', '#4ADE80'],
        ['2', 'Fill in student & parent details (one row per student)', '#60A5FA'],
        ['3', 'Upload the filled CSV — system auto-creates accounts', '#A78BFA'],
        ['4', 'New parent accounts get a temp password shown in results', '#FCD34D'],
      ] as [$n, $text, $color])
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <div style="width:24px;height:24px;border-radius:50%;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#000;flex-shrink:0;">{{ $n }}</div>
          <div style="font-size:12px;color:var(--gr);line-height:1.5;">{{ $text }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- CSV columns reference --}}
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div class="card-title">CSV Column Reference</div>
      <a href="{{ route('admin.import.sample.students') }}" class="btn btn-g btn-sm">⬇ Download Sample CSV</a>
    </div>
    <div class="tw">
      <table>
        <thead><tr><th>Column</th><th>Required</th><th>Example</th><th>Notes</th></tr></thead>
        <tbody>
          @foreach([
            ['student_name',    'Yes', 'Rahul Sharma',          ''],
            ['gender',          'Yes', 'M',                     'M / F / Other'],
            ['date_of_birth',   'Yes', '2012-05-15',            'YYYY-MM-DD format'],
            ['class_section',   'Yes', '5A',                    'e.g. 5A, 6B, 10C — no hyphens, hyphens are stripped automatically'],
            ['blood_group',     'No',  'A+',                    'A+, B+, O+, AB+, etc.'],
            ['known_conditions','No',  'Asthma',                'Leave blank if none'],
            ['parent_name',     'Yes', 'Rajesh Sharma',         ''],
            ['parent_email',    'Yes', 'rajesh@gmail.com',      'Unique — used to find or create parent account'],
            ['parent_phone',    'No',  '9876543210',            '10 digits only'],
          ] as [$col, $req, $ex, $note])
            <tr>
              <td><code style="font-size:11px;background:var(--lgr);padding:2px 6px;border-radius:4px;">{{ $col }}</code></td>
              <td><span class="badge {{ $req==='Yes'?'br':'bg' }}" style="font-size:10px;">{{ $req }}</span></td>
              <td style="font-size:12px;color:var(--gr);">{{ $ex }}</td>
              <td style="font-size:12px;color:var(--gr);">{{ $note }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="alert alert-y" style="margin-top:14px;">
      <span>💡</span>
      <span style="font-size:12px;">
        <strong>Parent email is the unique key.</strong>
        If a parent with that email already exists in the system, the new student is linked to their existing account — no duplicate parent is created.
        If multiple students in the file share the same parent email, all are linked to that one parent.
      </span>
    </div>
  </div>

  {{-- Upload form --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Upload CSV File</div></div>

    @if($errors->any())
      <div class="alert alert-r" style="margin-bottom:16px;">
        <span>⚠</span>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.import.students', $school) }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Excel or CSV File <span class="req">*</span></label>
        <input type="file" name="import_file" accept=".xlsx,.xls,.ods" class="form-input" required/>
        <div style="font-size:11px;color:var(--gr);margin-top:5px;">Accepted: .xlsx, .xls, .ods — Max 5 MB</div>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-g btn-lg">Import Students →</button>
        <a href="{{ route('admin.schools.show', $school) }}" class="btn btn-out">Cancel</a>
      </div>
    </form>
  </div>

</div>
@endsection
