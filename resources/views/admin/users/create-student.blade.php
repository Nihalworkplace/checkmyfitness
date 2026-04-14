@extends('layouts.app')
@section('title','Add Student')
@section('page-title','Add New Student')
@section('sidebar-nav')@include('admin.partials.nav')@endsection

@section('content')
<div style="max-width:700px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🎒 Add New Student</div>
      <a href="{{ route('admin.students') }}" class="btn btn-out btn-sm">← Back</a>
    </div>

    <div class="alert alert-b" style="margin-bottom:20px;">
      <span>ℹ️</span>
      <span style="font-size:13px;">A unique <strong>Reference Code</strong> (e.g. CMF-2024-06B-042) will be auto-generated. The parent can use this code to log in and view their child's health reports.</span>
    </div>

    <form method="POST" action="{{ route('admin.students.store') }}">
      @csrf

      <div class="form-group">
        <label class="form-label">Parent Account <span class="req">*</span></label>
        <select name="parent_id" class="form-input" required>
          <option value="">Select parent…</option>
          @foreach($parents as $p)
            <option value="{{ $p->id }}" {{ old('parent_id')==$p->id ? 'selected' : '' }}>
              {{ $p->name }} — {{ $p->email }}
            </option>
          @endforeach
        </select>
        <div class="form-hint">
          Create parent account first if not listed.
          <a href="{{ route('admin.parents.create') }}" style="color:var(--g);">Add parent →</a>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Student Name <span class="req">*</span></label>
          <input type="text" name="name" class="form-input" placeholder="Aarav Shah" value="{{ old('name') }}" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Gender <span class="req">*</span></label>
          <select name="gender" class="form-input" required>
            <option value="">Select…</option>
            <option value="M"     {{ old('gender')==='M'     ? 'selected' : '' }}>Male</option>
            <option value="F"     {{ old('gender')==='F'     ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ old('gender')==='Other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Date of Birth <span class="req">*</span></label>
          <input type="date" name="date_of_birth" class="form-input" value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Class / Section <span class="req">*</span></label>
          <select name="class_section" class="form-input" required>
            <option value="">Select class…</option>
            @foreach(config('cmf.classes', ['1A','1B','2A','2B','3A','3B','4A','4B','5A','5B','6A','6B','7A','7B','8A','8B','9A','9B','10A','10B','11A','11B','12A','12B']) as $cls)
              <option value="{{ $cls }}" {{ old('class_section')===$cls ? 'selected' : '' }}>Class {{ $cls }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- School dropdown --}}
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">School <span class="req">*</span></label>
          @if($schools->count())
            <select name="school_id" class="form-input" required>
              <option value="">Select school…</option>
              @foreach($schools as $school)
                <option value="{{ $school->id }}" {{ old('school_id')==$school->id ? 'selected' : '' }}>
                  {{ $school->name }} — {{ $school->city }}
                </option>
              @endforeach
            </select>
            <div class="form-hint">
              School not listed?
              <a href="{{ route('admin.schools.create') }}" style="color:var(--g);">Add school →</a>
            </div>
          @else
            <div class="alert alert-y">
              No schools added yet.
              <a href="{{ route('admin.schools.create') }}" style="color:var(--or);font-weight:700;">Add your first school →</a>
            </div>
          @endif
        </div>
        <div class="form-group">
          <label class="form-label">Blood Group</label>
          <select name="blood_group" class="form-input">
            <option value="">Unknown</option>
            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
              <option value="{{ $bg }}" {{ old('blood_group')===$bg ? 'selected' : '' }}>{{ $bg }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Known Medical Conditions</label>
        <textarea name="known_conditions" class="form-input" placeholder="Asthma, allergies, or any pre-existing conditions… (leave blank if none)">{{ old('known_conditions') }}</textarea>
      </div>

      <button type="submit" class="btn btn-g btn-lg btn-full">Create Student & Generate Reference Code →</button>
    </form>
  </div>
</div>
@endsection
