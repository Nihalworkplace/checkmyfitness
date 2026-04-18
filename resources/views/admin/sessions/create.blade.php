@extends('layouts.app')
@section('title','Create Session')
@section('page-title','Create Doctor Session')

@section('sidebar-nav')
@include('admin.partials.nav')
@endsection

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet"/>
<style>
.ts-wrapper.form-input { padding: 0; border: none; }
.ts-control {
  background: var(--lgr) !important;
  border: 1.5px solid var(--bd) !important;
  border-radius: 10px !important;
  min-height: 44px !important;
  padding: 6px 10px !important;
  font-size: 13px !important;
  color: var(--dk) !important;
  box-shadow: none !important;
}
.ts-control:focus-within,
.ts-wrapper.focus .ts-control { border-color: var(--g) !important; box-shadow: 0 0 0 3px rgba(29,158,117,0.12) !important; }
.ts-dropdown { border: 1.5px solid var(--bd) !important; border-radius: 10px !important; background: #fff !important; box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
.ts-dropdown .option { font-size: 13px !important; padding: 8px 12px !important; }
.ts-dropdown .option.active { background: var(--g) !important; color: #fff !important; }
.ts-dropdown .option:hover { background: rgba(29,158,117,0.08) !important; }
.ts-control .item {
  background: rgba(29,158,117,0.12) !important;
  color: var(--g) !important;
  border: 1px solid rgba(29,158,117,0.3) !important;
  border-radius: 6px !important;
  font-size: 12px !important;
  font-weight: 600 !important;
  padding: 2px 8px !important;
}
</style>
@endpush

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
          <label class="form-label">Doctors <span class="req">*</span></label>
          <select name="doctor_ids[]" id="doctor_select" multiple placeholder="Select one or more doctors…">
            @foreach($doctors as $d)
              @php
                $typeLabel = $d->doctor_type ? (\App\Models\User::DOCTOR_TYPES[$d->doctor_type] ?? $d->doctor_type) : 'No type set';
              @endphp
              <option value="{{ $d->id }}"
                data-type="{{ $d->doctor_type }}"
                data-type-label="{{ $typeLabel }}"
                {{ is_array(old('doctor_ids')) && in_array($d->id, old('doctor_ids')) ? 'selected' : '' }}>
                Dr. {{ $d->name }} ({{ $typeLabel }})
              </option>
            @endforeach
          </select>
          <div class="form-hint">Select multiple doctors — a separate session code is generated for each. Only active doctors are listed.</div>
          {{-- Selected doctor types summary --}}
          <div id="doctor-types-summary" style="display:none;margin-top:8px;padding:8px 12px;background:var(--lgr);border-radius:8px;font-size:12px;"></div>
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

      {{-- Classes — Tom Select multi-select --}}
      <div class="form-group">
        <label class="form-label">Classes Assigned</label>
        <select name="classes_assigned[]" id="classes_select" multiple placeholder="Pick classes or leave empty for all…">
          @foreach($classes as $cls)
            <option value="{{ $cls }}"
              {{ is_array(old('classes_assigned')) && in_array($cls, old('classes_assigned')) ? 'selected' : '' }}>
              {{ $cls }}
            </option>
          @endforeach
        </select>
        <div class="form-hint">Leave empty to allow all classes at that school. Type to search or click to pick.</div>
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
        <div style="font-size:11px;color:var(--gr);margin-top:6px;">e.g. SESS-DPS-20260329-A7X2 · One code per doctor · Valid for {{ (int) config('cmf.doctor_session_expiry_hours', 12) }} hours · Cannot be reused</div>
      </div>

      <button type="submit" class="btn btn-p btn-lg btn-full" id="submit-btn">Generate Session Code(s) & Assign to Doctor(s) →</button>
    </form>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// Tom Select for classes
new TomSelect('#classes_select', {
  plugins: ['remove_button'],
  maxOptions: 30,
  placeholder: 'Pick classes or leave empty for all…',
});

// Tom Select for doctors (multi)
const doctorTs = new TomSelect('#doctor_select', {
  plugins: ['remove_button', 'checkbox_options'],
  maxOptions: 100,
  placeholder: 'Select one or more doctors…',
  onChange: updateDoctorSummary,
});

function updateDoctorSummary() {
  const summary = document.getElementById('doctor-types-summary');
  const selected = doctorTs.getValue(); // array of selected ids
  const btn = document.getElementById('submit-btn');
  if (!selected || selected.length === 0) {
    summary.style.display = 'none';
    btn.textContent = 'Generate Session Code(s) & Assign to Doctor(s) →';
    return;
  }
  const typeColors = {
    general_physician: '#3B82F6', dentist: '#8B5CF6', eye_specialist: '#06B6D4',
    audiologist_ent: '#F59E0B', physiotherapist: '#10B981', psychologist: '#EC4899',
    lab_technician: '#EF4444',
  };
  let html = '<div style="font-size:11px;font-weight:700;color:var(--gr);margin-bottom:6px;text-transform:uppercase;">Sessions will be created for:</div>';
  selected.forEach(id => {
    const opt = document.querySelector('#doctor_select option[value="' + id + '"]');
    if (!opt) return;
    const label = opt.dataset.typeLabel || '';
    const type  = opt.dataset.type || '';
    const color = typeColors[type] || '#6B7280';
    html += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">'
          + '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + color + ';flex-shrink:0;"></span>'
          + '<span style="font-weight:600;color:var(--dk);">' + opt.text.split(' (')[0] + '</span>'
          + '<span style="color:' + color + ';font-size:10px;font-weight:700;">· ' + label + '</span>'
          + '</div>';
  });
  summary.innerHTML = html;
  summary.style.display = 'block';
  btn.textContent = 'Generate ' + selected.length + ' Session Code' + (selected.length > 1 ? 's' : '') + ' →';
}

// School details
function fillSchoolDetails(sel) {
  const opt = sel.options[sel.selectedIndex];
  const name = opt.dataset.name || '';
  const city = opt.dataset.city || '';
  document.getElementById('school_name').value = name;
  document.getElementById('school_city').value = city;
  const preview = document.getElementById('school_preview');
  if (name) {
    preview.innerHTML = '<strong style="color:var(--dk);">' + name + '</strong><span style="color:var(--gr);margin-left:8px;">— ' + city + '</span>';
  } else {
    preview.textContent = 'Select a school above…';
  }
}

// Pre-fill school on load
document.addEventListener('DOMContentLoaded', function(){
  const sel = document.getElementById('school_id');
  if (sel && sel.value) fillSchoolDetails(sel);
  updateDoctorSummary();
});
</script>
@endpush
@endsection
