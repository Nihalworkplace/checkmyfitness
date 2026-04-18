@extends('layouts.app')
@section('title','Checkup — '.$student->name)
@section('page-title','Student Checkup')

@section('sidebar-nav')
<a href="{{ route('doctor.session.active') }}" class="ni"><div class="ni-ico" style="background:rgba(59,130,246,0.25);">← </div> Back to Session</a>
<a href="{{ route('doctor.completed') }}" class="ni"><div class="ni-ico" style="background:rgba(29,158,117,0.2);">✅</div> Completed Today</a>
<a href="{{ route('doctor.summary') }}" class="ni"><div class="ni-ico" style="background:rgba(245,158,11,0.2);">📊</div> Summary</a>
@endsection

@push('head')
<link rel="stylesheet" href="{{ asset('css/checkup-form.css') }}" />
@endpush

@section('content')

@php
  // Sections this doctor can access
  $sections = \App\Models\User::DOCTOR_TYPE_SECTIONS[$doctorType] ?? [];
  $canSee = fn(string $s): bool => in_array($s, $sections);
  $typeLabel = \App\Models\User::DOCTOR_TYPES[$doctorType] ?? 'Unknown';
@endphp

{{-- Student header --}}
<div class="page-header mb-16">
  <div class="page-header__left">
    <div class="avatar avatar--lg {{ $student->gender === 'M' ? 'avatar--male' : 'avatar--female' }}">
      {{ strtoupper(substr($student->name, 0, 1)) }}
    </div>
    <div class="page-header__body">
      <div class="page-header__title">{{ $student->name }}</div>
      <div class="page-header__sub-sm">
        Class {{ $student->class_section }} · {{ $student->gender === 'M' ? 'Male' : 'Female' }} · Age {{ $student->age }} · Ref: {{ $student->reference_code }} · Blood: {{ $student->blood_group ?? 'Unknown' }}
      </div>
    </div>
  </div>
  <div class="text-right">
    <div style="font-size:10px;color:var(--gr);margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Your Role</div>
    <span class="badge bb" style="font-size:11px;">{{ $typeLabel }}</span>
    @if($checkup)
      <span class="badge {{ $checkup->status === 'completed' ? 'bg' : 'by' }}" style="margin-left:4px;">{{ ucfirst($checkup->status) }}</span>
      @if($checkup->overall_score)
        <div style="font-family:var(--ff);font-size:26px;font-weight:900;color:{{ $checkup->overall_score>=75?'#4ADE80':($checkup->overall_score>=55?'#FCD34D':'#EF4444') }};margin-top:4px;">
          {{ $checkup->overall_score }}<span style="font-size:14px;color:rgba(255,255,255,0.4);">/100</span>
        </div>
      @endif
    @else
      <span class="badge bb" style="margin-left:4px;">New Checkup</span>
    @endif
  </div>
</div>

{{-- Alerts from previous --}}
@if($checkup && count($checkup->alerts ?? []) > 0)
  <div class="alert alert-r" style="margin-bottom:14px;">
    <span style="font-size:14px;flex-shrink:0;">⚠️</span>
    <div>
      <strong>Existing Alerts:</strong>
      @foreach($checkup->alerts as $a)<div>{{ $a }}</div>@endforeach
    </div>
  </div>
@endif

<form method="POST" action="{{ route('doctor.checkup.save', $student) }}" id="checkup-form">
@csrf

@php
  $v = fn($field) => old($field, $checkup?->{$field});
  function paramColor($val, $lo, $hi): string {
    if($val===null) return '#94A3B8';
    if($val>=$lo && $val<=$hi) return '#1D9E75';
    $diff = ($val<$lo) ? ($lo-$val)/$lo : ($val-$hi)/$hi;
    return $diff>0.25 ? '#EF4444' : '#F59E0B';
  }
@endphp

{{-- ── PHYSICAL & VITALS — General Physician ── --}}
@if($canSee('physical'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">📏</div><div class="pg-title">Physical & Vitals</div><div class="pg-count">7 parameters</div></div>

  {{-- Height --}}
  @php $hVal = $v('height_cm') ?? 145; $hC = paramColor($hVal, 100, 200); @endphp
  <div class="pe">
    <div><div class="pe-name">Height</div><div class="pe-unit">cm · normal: 100–200</div></div>
    <input type="range" class="slider" id="sl-height_cm" min="80" max="210" value="{{ $hVal }}" style="accent-color:{{ $hC }};" oninput="sync('height_cm',this.value)"/>
    <input type="number" class="numval" id="nv-height_cm" name="height_cm" value="{{ $hVal }}" min="80" max="210" style="color:{{ $hC }};" onchange="syncBack('height_cm',this.value)"/>
    <span class="status-pill" id="sp-height_cm" style="background:{{ $hC }}18;color:{{ $hC }};">{{ $hVal >= 100 && $hVal <= 200 ? 'Normal' : 'Check' }}</span>
  </div>

  {{-- Weight --}}
  @php $wVal = $v('weight_kg') ?? 40; $wC = paramColor($wVal, 20, 120); @endphp
  <div class="pe">
    <div><div class="pe-name">Weight</div><div class="pe-unit">kg · normal: 20–80</div></div>
    <input type="range" class="slider" id="sl-weight_kg" min="10" max="150" value="{{ $wVal }}" style="accent-color:{{ $wC }};" oninput="syncBmi('weight_kg',this.value)"/>
    <input type="number" class="numval" id="nv-weight_kg" name="weight_kg" value="{{ $wVal }}" min="10" max="150" style="color:{{ $wC }};" onchange="syncBmi('weight_kg',this.value)"/>
    <span class="status-pill" style="background:{{ $wC }}18;color:{{ $wC }};">OK</span>
  </div>

  {{-- BMI (auto-calculated) --}}
  <div class="pe" style="background:var(--lgr);">
    <div><div class="pe-name">BMI</div><div class="pe-unit">auto-calculated · normal: 18.5–24.9</div></div>
    <div style="font-size:13px;color:var(--gr);">Calculated from height & weight</div>
    <input type="text" class="numval" id="nv-bmi" name="bmi" value="{{ $v('bmi') ?? '' }}" readonly style="background:var(--lgr);border-color:var(--bd);color:var(--gr);"/>
    <span class="status-pill" id="sp-bmi" style="background:var(--lgr);color:var(--gr);">Auto</span>
  </div>

  {{-- Heart Rate --}}
  @php $hrVal = $v('heart_rate_bpm') ?? 80; $hrC = paramColor($hrVal, 60, 100); @endphp
  <div class="pe {{ ($hrVal < 60 || $hrVal > 100) ? 'low' : '' }}">
    <div><div class="pe-name">Heart Rate</div><div class="pe-unit">bpm · normal: 60–100</div></div>
    <input type="range" class="slider" id="sl-heart_rate_bpm" min="30" max="220" value="{{ $hrVal }}" style="accent-color:{{ $hrC }};" oninput="sync('heart_rate_bpm',this.value)"/>
    <input type="number" class="numval" id="nv-heart_rate_bpm" name="heart_rate_bpm" value="{{ $hrVal }}" min="30" max="220" style="color:{{ $hrC }};" onchange="syncBack('heart_rate_bpm',this.value)"/>
    <span class="status-pill" style="background:{{ $hrC }}18;color:{{ $hrC }};">{{ $hrVal>=60&&$hrVal<=100?'Normal':($hrVal<60?'Low':'High') }}</span>
  </div>

  {{-- BP --}}
  <div class="pe" style="grid-template-columns:140px 1fr 1fr;">
    <div><div class="pe-name">Blood Pressure</div><div class="pe-unit">mmHg · normal: 90–130 / 60–90</div></div>
    <input type="number" class="numval" name="bp_systolic" placeholder="Sys" value="{{ $v('bp_systolic') }}" min="60" max="200" style="width:80px;"/>
    <input type="number" class="numval" name="bp_diastolic" placeholder="Dia" value="{{ $v('bp_diastolic') }}" min="40" max="130" style="width:80px;"/>
  </div>

  {{-- Temperature --}}
  @php $tVal = $v('temperature_f') ?? 98.4; $tC = paramColor($tVal, 97, 99); @endphp
  <div class="pe {{ ($tVal < 97 || $tVal > 99) ? 'low' : '' }}">
    <div><div class="pe-name">Temperature</div><div class="pe-unit">°F · normal: 97–99</div></div>
    <input type="range" class="slider" id="sl-temperature_f" min="90" max="108" step="0.1" value="{{ $tVal }}" style="accent-color:{{ $tC }};" oninput="sync('temperature_f',this.value)"/>
    <input type="number" class="numval" id="nv-temperature_f" name="temperature_f" value="{{ $tVal }}" min="90" max="108" step="0.1" style="color:{{ $tC }};" onchange="syncBack('temperature_f',this.value)"/>
    <span class="status-pill" style="background:{{ $tC }}18;color:{{ $tC }};">{{ $tVal>=97&&$tVal<=99?'Normal':($tVal<97?'Low':'⚠ Fever') }}</span>
  </div>

  {{-- SpO2 --}}
  @php $o2Val = $v('spo2_percent') ?? 98; $o2C = paramColor($o2Val, 95, 100); @endphp
  <div class="pe {{ $o2Val < 95 ? 'low' : '' }}">
    <div><div class="pe-name">SpO2 / Oxygen</div><div class="pe-unit">% · normal: 95–100</div></div>
    <input type="range" class="slider" id="sl-spo2_percent" min="70" max="100" value="{{ $o2Val }}" style="accent-color:{{ $o2C }};" oninput="sync('spo2_percent',this.value)"/>
    <input type="number" class="numval" id="nv-spo2_percent" name="spo2_percent" value="{{ $o2Val }}" min="70" max="100" style="color:{{ $o2C }};" onchange="syncBack('spo2_percent',this.value)"/>
    <span class="status-pill" style="background:{{ $o2C }}18;color:{{ $o2C }};">{{ $o2Val>=95?'Normal':'⚠ Low O2' }}</span>
  </div>
</div>
@endif

{{-- ── DENTAL — Dentist ── --}}
@if($canSee('dental'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">🦷</div><div class="pg-title">Dental Health</div><div class="pg-count">1 parameter</div></div>
  @php $dVal = $v('dental_score') ?? 7; $dC = $dVal>=7?'#1D9E75':($dVal>=5?'#F59E0B':'#EF4444'); @endphp
  <div class="pe {{ $dVal < 5 ? 'low' : '' }}">
    <div><div class="pe-name">Dental Health</div><div class="pe-unit">score /10 · normal: 7–10</div></div>
    <input type="range" class="slider" id="sl-dental_score" min="1" max="10" value="{{ $dVal }}" style="accent-color:{{ $dC }};" oninput="sync('dental_score',this.value)"/>
    <input type="number" class="numval" id="nv-dental_score" name="dental_score" value="{{ $dVal }}" min="1" max="10" style="color:{{ $dC }};" onchange="syncBack('dental_score',this.value)"/>
    <span class="status-pill" style="background:{{ $dC }}18;color:{{ $dC }};">{{ $dVal>=7?'Good':($dVal>=5?'Average':'Low') }}</span>
  </div>
</div>
@endif

{{-- ── EYE — Eye Specialist ── --}}
@if($canSee('eye'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">👁️</div><div class="pg-title">Vision & Eye Health</div><div class="pg-count">3 parameters</div></div>
  <div class="pe" style="grid-template-columns:140px 1fr 1fr;">
    <div><div class="pe-name">Vision</div><div class="pe-unit">Snellen · normal: 20/20</div></div>
    <div><label style="font-size:10px;color:var(--gr);">Left Eye</label><input type="text" name="vision_left" class="numval" style="width:80px;" placeholder="20/20" value="{{ $v('vision_left') ?? '20/20' }}"/></div>
    <div><label style="font-size:10px;color:var(--gr);">Right Eye</label><input type="text" name="vision_right" class="numval" style="width:80px;" placeholder="20/20" value="{{ $v('vision_right') ?? '20/20' }}"/></div>
  </div>
  @foreach([
    ['eye_strain', 'Eye Strain', ['None','Mild','Severe'], ['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name, $label, $opts, $colors])
  <div class="pe" style="grid-template-columns:140px 1fr;">
    <div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">
      @foreach($opts as $oi => $opt)
        <button type="button" class="qs {{ $v($name)===$opt?'on':'' }}"
          style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}"
          onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>
      @endforeach
    </div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
</div>
@endif

{{-- ── HEARING — Audiologist/ENT ── --}}
@if($canSee('hearing'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">👂</div><div class="pg-title">Hearing Assessment</div><div class="pg-count">1 parameter</div></div>
  @foreach([
    ['hearing', 'Hearing', ['Normal','Mild Issue','Needs Test'], ['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name, $label, $opts, $colors])
  <div class="pe" style="grid-template-columns:140px 1fr;">
    <div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">
      @foreach($opts as $oi => $opt)
        <button type="button" class="qs {{ $v($name)===$opt?'on':'' }}"
          style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}"
          onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>
      @endforeach
    </div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
</div>
@endif

{{-- ── LAB & BIOCHEMICAL — Lab Technician ── --}}
@if($canSee('lab'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">🧪</div><div class="pg-title">Lab & Biochemical</div><div class="pg-count">4 parameters</div></div>
  @php $hbVal = $v('haemoglobin_gdl') ?? ($student->gender==='F' ? 12 : 13); $lo = $student->gender==='F' ? 11.5 : 13; $hbC = paramColor($hbVal, $lo, 17); @endphp
  <div class="pe {{ $hbVal < $lo ? 'low' : '' }}">
    <div><div class="pe-name">Haemoglobin</div><div class="pe-unit">g/dL · normal: {{ $lo }}–17</div></div>
    <input type="range" class="slider" id="sl-haemoglobin_gdl" min="3" max="20" step="0.1" value="{{ $hbVal }}" style="accent-color:{{ $hbC }};" oninput="sync('haemoglobin_gdl',this.value)"/>
    <input type="number" class="numval" id="nv-haemoglobin_gdl" name="haemoglobin_gdl" value="{{ $hbVal }}" min="3" max="20" step="0.1" style="color:{{ $hbC }};" onchange="syncBack('haemoglobin_gdl',this.value)"/>
    <span class="status-pill" style="background:{{ $hbC }}18;color:{{ $hbC }};">{{ $hbVal>=$lo?'Normal':'⚠ Low' }}</span>
  </div>
  @php $vdVal = $v('vitamin_d_ngml') ?? 35; $vdC = paramColor($vdVal, 30, 80); @endphp
  <div class="pe {{ $vdVal < 30 ? 'low' : '' }}">
    <div><div class="pe-name">Vitamin D</div><div class="pe-unit">ng/mL · normal: 30–80</div></div>
    <input type="range" class="slider" id="sl-vitamin_d_ngml" min="3" max="100" value="{{ $vdVal }}" style="accent-color:{{ $vdC }};" oninput="sync('vitamin_d_ngml',this.value)"/>
    <input type="number" class="numval" id="nv-vitamin_d_ngml" name="vitamin_d_ngml" value="{{ $vdVal }}" min="3" max="100" style="color:{{ $vdC }};" onchange="syncBack('vitamin_d_ngml',this.value)"/>
    <span class="status-pill" style="background:{{ $vdC }}18;color:{{ $vdC }};">{{ $vdVal>=30?'Normal':'Deficient' }}</span>
  </div>
  @foreach([
    ['iron_level','Iron Level',['Normal','Low','Very Low'],['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name,$label,$opts,$colors])
  <div class="pe" style="grid-template-columns:140px 1fr;"><div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">@foreach($opts as $oi=>$opt)<button type="button" class="qs {{ $v($name)===$opt?'on':'' }}" style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}" onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>@endforeach</div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
  @php $bsVal = $v('blood_sugar_mgdl') ?? 90; $bsC = paramColor($bsVal, 70, 140); @endphp
  <div class="pe">
    <div><div class="pe-name">Blood Sugar</div><div class="pe-unit">mg/dL · normal: 70–140</div></div>
    <input type="range" class="slider" id="sl-blood_sugar_mgdl" min="40" max="400" value="{{ $bsVal }}" style="accent-color:{{ $bsC }};" oninput="sync('blood_sugar_mgdl',this.value)"/>
    <input type="number" class="numval" id="nv-blood_sugar_mgdl" name="blood_sugar_mgdl" value="{{ $bsVal }}" min="40" max="400" style="color:{{ $bsC }};" onchange="syncBack('blood_sugar_mgdl',this.value)"/>
    <span class="status-pill" style="background:{{ $bsC }}18;color:{{ $bsC }};">{{ $bsVal>=70&&$bsVal<=140?'Normal':($bsVal<70?'Low':'High') }}</span>
  </div>
</div>
@endif

{{-- ── MUSCULOSKELETAL — Physiotherapist ── --}}
@if($canSee('musculoskeletal'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">🦴</div><div class="pg-title">Musculoskeletal</div><div class="pg-count">4 parameters</div></div>
  @foreach([
    ['posture','Posture',['Good','Mild Curve','Scoliosis Risk'],['#1D9E75','#F59E0B','#EF4444']],
    ['flexibility','Flexibility',['Good','Average','Poor'],['#1D9E75','#F59E0B','#EF4444']],
    ['flat_feet','Flat Feet',['None','Mild','Moderate'],['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name,$label,$opts,$colors])
  <div class="pe" style="grid-template-columns:140px 1fr;"><div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">@foreach($opts as $oi=>$opt)<button type="button" class="qs {{ $v($name)===$opt?'on':'' }}" style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}" onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>@endforeach</div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
  @php $gsVal = $v('grip_strength_score') ?? 7; $gsC = $gsVal>=7?'#1D9E75':($gsVal>=5?'#F59E0B':'#EF4444'); @endphp
  <div class="pe">
    <div><div class="pe-name">Grip Strength</div><div class="pe-unit">score /10 · normal: 6–10</div></div>
    <input type="range" class="slider" id="sl-grip_strength_score" min="1" max="10" value="{{ $gsVal }}" style="accent-color:{{ $gsC }};" oninput="sync('grip_strength_score',this.value)"/>
    <input type="number" class="numval" id="nv-grip_strength_score" name="grip_strength_score" value="{{ $gsVal }}" min="1" max="10" style="color:{{ $gsC }};" onchange="syncBack('grip_strength_score',this.value)"/>
    <span class="status-pill" style="background:{{ $gsC }}18;color:{{ $gsC }};">{{ $gsVal>=6?'Good':($gsVal>=4?'Average':'Low') }}</span>
  </div>
</div>
@endif

{{-- ── WELLNESS & MENTAL — Psychologist ── --}}
@if($canSee('mental'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">🧠</div><div class="pg-title">Wellness & Mental Health</div><div class="pg-count">3 parameters</div></div>
  @php $msVal = $v('mental_score') ?? 7; $msC = $msVal>=7?'#1D9E75':($msVal>=5?'#F59E0B':'#EF4444'); @endphp
  <div class="pe {{ $msVal < 5 ? 'low' : '' }}">
    <div><div class="pe-name">Mental Well-being</div><div class="pe-unit">score /10 · normal: 7–10</div></div>
    <input type="range" class="slider" id="sl-mental_score" min="1" max="10" value="{{ $msVal }}" style="accent-color:{{ $msC }};" oninput="sync('mental_score',this.value)"/>
    <input type="number" class="numval" id="nv-mental_score" name="mental_score" value="{{ $msVal }}" min="1" max="10" style="color:{{ $msC }};" onchange="syncBack('mental_score',this.value)"/>
    <span class="status-pill" style="background:{{ $msC }}18;color:{{ $msC }};">{{ $msVal>=7?'Good':($msVal>=5?'Average':'Low ⚠') }}</span>
  </div>
  @foreach([
    ['stress_level','Stress Level',['Low','Moderate','High'],['#1D9E75','#F59E0B','#EF4444']],
    ['sleep_quality','Sleep Quality',['Good','Average','Poor'],['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name,$label,$opts,$colors])
  <div class="pe" style="grid-template-columns:140px 1fr;"><div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">@foreach($opts as $oi=>$opt)<button type="button" class="qs {{ $v($name)===$opt?'on':'' }}" style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}" onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>@endforeach</div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
</div>
@endif

{{-- ── SKIN & HAIR — General Physician ── --}}
@if($canSee('skin'))
<div class="param-group">
  <div class="pg-head"><div class="pg-ico">💇</div><div class="pg-title">Skin & Hair</div><div class="pg-count">2 parameters</div></div>
  @foreach([
    ['skin_health','Skin Health',['Healthy','Mild Issue','Needs Attention'],['#1D9E75','#F59E0B','#EF4444']],
    ['hair_health','Hair & Scalp',['Healthy','Mild Issue','Needs Attention'],['#1D9E75','#F59E0B','#EF4444']],
  ] as [$name,$label,$opts,$colors])
  <div class="pe" style="grid-template-columns:140px 1fr;"><div><div class="pe-name">{{ $label }}</div></div>
    <div class="qs-row">@foreach($opts as $oi=>$opt)<button type="button" class="qs {{ $v($name)===$opt?'on':'' }}" style="{{ $v($name)===$opt?'background:'.$colors[$oi].';border-color:'.$colors[$oi]:'' }}" onclick="setQuick('{{ $name }}','{{ $opt }}','{{ $colors[$oi] }}',this)">{{ $opt }}</button>@endforeach</div>
    <input type="hidden" name="{{ $name }}" id="qv-{{ $name }}" value="{{ $v($name) ?? $opts[0] }}"/>
  </div>
  @endforeach
</div>
@endif

{{-- ── NOTES — Common for ALL doctors ── --}}
<div class="card">
  <div class="card-title" style="margin-bottom:14px;">📝 Doctor's Notes & Recommendations</div>
  <div style="font-size:11px;color:var(--gr);margin-bottom:12px;background:var(--lgr);padding:8px 12px;border-radius:8px;">
    These fields are shared — previous entries from other specialists are preserved. Add your own observations below.
  </div>
  <div class="form-grid">
    <div class="form-group">
      <label class="form-label">Doctor's Observations</label>
      <textarea name="doctor_notes" class="form-input" rows="4" placeholder="Your observations, specific findings, anything notable during examination…">{{ $v('doctor_notes') }}</textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Recommendations for Parent/School</label>
      <textarea name="recommendations" class="form-input" rows="4" placeholder="Dietary recommendations, follow-up tests, specialist referrals, lifestyle changes…">{{ $v('recommendations') }}</textarea>
    </div>
  </div>
</div>

{{-- ── ACTION BUTTONS ── --}}
<div style="background:#fff;border:1.5px solid var(--bd);border-radius:16px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;position:sticky;bottom:16px;box-shadow:0 8px 32px rgba(0,0,0,0.1);">
  <div>
    <a href="{{ route('doctor.session.active') }}" class="btn btn-out">← Back to Session</a>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <button type="submit" name="status" value="draft" class="btn btn-out" style="border-color:var(--or);color:var(--or);">💾 Save as Draft</button>
    <button type="submit" name="status" value="completed" class="btn btn-g btn-lg">✓ Complete Checkup & Next →</button>
  </div>
</div>

</form>

@push('scripts')
<script>
function sync(field, val){
  val = parseFloat(val);
  const nv = document.getElementById('nv-'+field);
  if(nv){ nv.value = val; updateColor(field, val); }
  if(field==='height_cm'||field==='weight_kg') calcBmi();
}
function syncBack(field, val){
  val = parseFloat(val);
  const sl = document.getElementById('sl-'+field);
  if(sl) sl.value = val;
  updateColor(field, val);
  if(field==='height_cm'||field==='weight_kg') calcBmi();
}
function syncBmi(field, val){ sync(field, val); calcBmi(); }
function updateColor(field, val){
  const nv = document.getElementById('nv-'+field);
  const sl = document.getElementById('sl-'+field);
  const sp = document.getElementById('sp-'+field);
  const normals = { height_cm:[100,200], weight_kg:[20,120], heart_rate_bpm:[60,100], temperature_f:[97,99], spo2_percent:[95,100], haemoglobin_gdl:[11.5,17], vitamin_d_ngml:[30,80], blood_sugar_mgdl:[70,140], dental_score:[7,10], mental_score:[7,10], grip_strength_score:[6,10] };
  if(!normals[field]) return;
  const [lo,hi] = normals[field];
  let c = val>=lo&&val<=hi ? '#1D9E75' : (Math.abs(val<lo?lo-val:val-hi)/(lo||1) > 0.25 ? '#EF4444' : '#F59E0B');
  if(nv) nv.style.color=c;
  if(sl) sl.style.accentColor=c;
  if(sp){ sp.style.background=c+'18'; sp.style.color=c; sp.textContent=val>=lo&&val<=hi?'Normal':(val<lo?'Low':'High'); }
}
function calcBmi(){
  const h = parseFloat(document.getElementById('nv-height_cm')?.value);
  const w = parseFloat(document.getElementById('nv-weight_kg')?.value);
  const bmiEl = document.getElementById('nv-bmi');
  const spEl  = document.getElementById('sp-bmi');
  if(h>0&&w>0&&bmiEl){
    const bmi = Math.round((w/((h/100)**2))*10)/10;
    bmiEl.value = bmi;
    const c = bmi>=18.5&&bmi<=24.9?'#1D9E75':(bmi<15||bmi>30?'#EF4444':'#F59E0B');
    bmiEl.style.color = c;
    if(spEl){ spEl.style.background=c+'18'; spEl.style.color=c; spEl.textContent=bmi>=18.5&&bmi<=24.9?'Normal':(bmi<18.5?'Underweight':'Overweight'); }
  }
}
function setQuick(field, val, color, btn){
  document.getElementById('qv-'+field).value = val;
  btn.closest('.qs-row').querySelectorAll('.qs').forEach(b=>{ b.classList.remove('on'); b.style.background=''; b.style.borderColor=''; b.style.color=''; });
  btn.classList.add('on'); btn.style.background=color; btn.style.borderColor=color; btn.style.color='#fff';
}
</script>
@endpush
@endsection
