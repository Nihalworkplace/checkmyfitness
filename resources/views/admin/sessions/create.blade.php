@extends('layouts.app')
@section('title','Create Session')
@section('page-title','Create Doctor Session')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@section('content')
<div style="max-width:700px;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🔑 Generate New Doctor Session</div>
      <a href="{{ route('admin.sessions.index') }}" class="btn btn-out btn-sm">← Back to Sessions</a>
    </div>

    <div class="alert alert-b" style="margin-bottom:20px;">
      <span style="font-size:14px;">ℹ️</span>
      <span style="font-size:13px;">A unique, time-limited session code will be generated automatically. Share it with the doctor <strong>on the day of the visit</strong>. Old codes are never reused.</span>
    </div>

    <form method="POST" action="{{ route('admin.sessions.store') }}">
      @csrf

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Doctor <span class="req">*</span></label>
          <select name="doctor_id" class="form-input" required>
            <option value="">Select doctor…</option>
            @foreach($doctors as $d)
              <option value="{{ $d->id }}" {{ old('doctor_id')==$d->id?'selected':'' }}>
                Dr. {{ $d->name }} — {{ $d->staff_code }}
              </option>
            @endforeach
          </select>
          <div class="form-hint">Only active doctors are listed.</div>
        </div>
        <div class="form-group">
          <label class="form-label">Visit Date <span class="req">*</span></label>
          <input type="date" name="visit_date" class="form-input" value="{{ old('visit_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required/>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">School <span class="req">*</span></label>
          @if($schools->count())
            <select name="school_id" id="school_id" class="form-input" required onchange="fillSchoolDetails(this)">
              <option value="">Select school…</option>
              @foreach($schools as $school)
                <option value="{{ $school->id }}"
                  data-name="{{ $school->name }}"
                  data-city="{{ $school->city }}"
                  {{ old('school_id')==$school->id?'selected':'' }}>
                  {{ $school->name }} — {{ $school->city }}
                </option>
              @endforeach
            </select>
            <div class="form-hint">
              School not listed?
              <a href="{{ route('admin.schools.create') }}" style="color:var(--g);">Add school first →</a>
            </div>
          @else
            <div class="alert alert-y">
              No schools added yet.
              <a href="{{ route('admin.schools.create') }}" style="color:var(--or);font-weight:700;">Add your first school →</a>
            </div>
          @endif
          {{-- Hidden fields populated by JS --}}
          <input type="hidden" name="school_name" id="school_name" value="{{ old('school_name') }}"/>
          <input type="hidden" name="school_city" id="school_city" value="{{ old('school_city') }}"/>
        </div>
        <div class="form-group">
          <label class="form-label">School (preview)</label>
          <div id="school_preview" style="background:var(--lgr);border-radius:10px;padding:11px 13px;font-size:13px;color:var(--gr);min-height:44px;display:flex;align-items:center;">
            Select a school above…
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Classes Assigned</label>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;" id="class-chips">
          @foreach(['1A','1B','2A','2B','3A','3B','4A','4B','5A','5B','6A','6B','7A','7B','8A','8B','9A','9B','10A','10B','11A','11B','12A','12B'] as $cls)
            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;">
              <input type="checkbox" name="classes_assigned[]" value="{{ $cls }}"
                {{ is_array(old('classes_assigned')) && in_array($cls, old('classes_assigned')) ? 'checked' : '' }}
                style="accent-color:var(--g);">
              <span style="font-size:12px;font-weight:600;">{{ $cls }}</span>
            </label>
          @endforeach
        </div>
        <div class="form-hint">Leave unchecked to allow all classes at that school.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Admin Notes</label>
        <textarea name="admin_notes" class="form-input" placeholder="Any special instructions for this session…">{{ old('admin_notes') }}</textarea>
      </div>

      <div style="background:var(--lgr);border-radius:12px;padding:16px;margin-bottom:20px;">
        <div style="font-size:12px;font-weight:700;color:var(--gr);margin-bottom:8px;">SESSION CODE WILL BE GENERATED AS:</div>
        <div style="font-family:monospace;font-size:16px;font-weight:700;color:var(--dk);">
          SESS-<span style="color:var(--bl);">[SCHOOL]</span>-<span style="color:var(--pu);">[DATE]</span>-<span style="color:var(--g);">[RANDOM4]</span>
        </div>
        <div style="font-size:11px;color:var(--gr);margin-top:6px;">e.g. SESS-DPS-20260329-A7X2 · Valid for {{ (int) config('cmf.doctor_session_expiry_hours', 12) }} hours · Cannot be reused</div>
      </div>

      <button type="submit" class="btn btn-p btn-lg btn-full">Generate Session Code & Assign to Doctor →</button>
    </form>
  </div>
</div>

@push('scripts')
<script>
function fillSchoolDetails(sel) {
  const opt = sel.options[sel.selectedIndex];
  const name = opt.dataset.name || '';
  const city = opt.dataset.city || '';
  document.getElementById('school_name').value = name;
  document.getElementById('school_city').value = city;
  const preview = document.getElementById('school_preview');
  if(name) {
    preview.innerHTML = '<strong style="color:var(--dk);">' + name + '</strong><span style="color:var(--gr);margin-left:8px;">— ' + city + '</span>';
  } else {
    preview.textContent = 'Select a school above…';
  }
}
// Pre-fill if old value exists
document.addEventListener('DOMContentLoaded', function(){
  const sel = document.getElementById('school_id');
  if(sel && sel.value) fillSchoolDetails(sel);
});
</script>
@endpush
@endsection
