@extends('layouts.app')
@section('title','Import Doctors')
@section('page-title','Import Doctors')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')

<div style="max-width:680px;">

  {{-- Header --}}
  <div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;">
    <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,0.35);letter-spacing:1.5px;margin-bottom:6px;">BULK IMPORT</div>
    <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:#fff;margin-bottom:4px;">Import Doctors</div>
    <div style="font-size:13px;color:rgba(255,255,255,0.45);">Upload a CSV to create multiple doctor accounts at once</div>
  </div>

  {{-- Column reference --}}
  <div class="card" style="margin-bottom:18px;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
      <div class="card-title">CSV Column Reference</div>
      <a href="{{ route('admin.import.sample.doctors') }}" class="btn btn-b btn-sm">⬇ Download Sample CSV</a>
    </div>
    <div class="tw">
      <table>
        <thead><tr><th>Column</th><th>Required</th><th>Example</th><th>Notes</th></tr></thead>
        <tbody>
          @php
            $typeDesc = collect(\App\Models\Doctor::DOCTOR_TYPES)
                          ->map(fn($label, $key) => "{$key} — {$label}")
                          ->implode(', ');
          @endphp
          @foreach([
            ['name',           'Yes', 'Dr. Priya Nair',      ''],
            ['staff_code',     'Yes', 'CMF-DOC-001',         'Must be unique — doctors log in with this code'],
            ['license_number', 'Yes', 'MCI-GUJ-2018-10234', 'State Medical Council registration number — must be unique'],
            ['doctor_type',    'Yes', 'general_physician',   'Required. Valid values: ' . $typeDesc],
            ['phone',          'No',  '9876543210',          ''],
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
      <span style="font-size:12px;"><strong>Staff code is the unique key.</strong> Rows with a staff code that already exists in the system will be skipped — no duplicate doctors created.</span>
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

    <form method="POST" action="{{ route('admin.import.doctors') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Excel or CSV File <span class="req">*</span></label>
        <input type="file" name="import_file" accept=".xlsx,.xls,.ods" class="form-input" required/>
        <div style="font-size:11px;color:var(--gr);margin-top:5px;">Accepted: .xlsx, .xls, .ods — Max 2 MB</div>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" class="btn btn-b btn-lg">Import Doctors →</button>
        <a href="{{ route('admin.doctors') }}" class="btn btn-out">Cancel</a>
      </div>
    </form>
  </div>

</div>
@endsection
