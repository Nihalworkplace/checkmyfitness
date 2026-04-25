{{-- resources/views/parent/report.blade.php --}}
@extends('layouts.app')
@section('title','Health Report — '.$student->name)
@section('page-title','Health Report')
@section('sidebar-nav')
@include('parent.partials.nav')
@endsection

@section('content')

@if($allCheckups->isEmpty())
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">🩺</div>
    <div style="font-size:16px;font-weight:700;color:var(--gr);">No checkup completed yet for {{ $student->name }}.</div>
    <div style="font-size:13px;color:var(--bd);margin-top:8px;">Checkup reports will appear here after the doctor's visit.</div>
  </div>
@else

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
  $typeIcons = [
    'general_physician' => '📏',
    'dentist'           => '🦷',
    'eye_specialist'    => '👁️',
    'audiologist_ent'   => '👂',
    'physiotherapist'   => '🦴',
    'psychologist'      => '🧠',
    'lab_technician'    => '🧪',
  ];

  // Merge all parameter values across all checkups.
  // For each field, take the value from the most recent checkup that has it.
  $merged = [];
  foreach ($allCheckups as $c) {
    foreach ($c->getFillable() as $field) {
      if (!isset($merged[$field]) && $c->$field !== null && $c->$field !== '') {
        $merged[$field] = $c->$field;
      }
    }
  }

  // Collect all alerts from all checkups
  $allAlerts = $allCheckups->flatMap(fn($c) => $c->alerts ?? [])->values();

  // Best overall score (highest across all checkups)
  $bestScore = $allCheckups->max('overall_score');

  // All notes — collect per checkup, skip blanks
  $allNotes = $allCheckups->filter(fn($c) => $c->doctor_notes || $c->recommendations);
@endphp

  {{-- ── REPORT HEADER ── --}}
  <div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $student->gender==='M'?'#3B82F6':'#8B5CF6' }};display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0;">
      {{ strtoupper(substr($student->name,0,1)) }}
    </div>
    <div style="flex:1;">
      <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:#fff;">{{ $student->name }}'s Health Report</div>
      <div style="font-size:12px;color:rgba(255,255,255,0.45);margin-top:3px;">
        {{ $student->school_name }} · Class {{ $student->class_section }} · Age {{ $student->age }}
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
        @foreach($allCheckups as $c)
          @php $dt = $c->doctor?->doctor_type; $dc = $typeColors[$dt] ?? '#6B7280'; @endphp
          <span style="font-size:10px;font-weight:700;background:{{ $dc }}22;color:{{ $dc }};border:1px solid {{ $dc }}44;padding:2px 8px;border-radius:20px;">
            {{ $typeIcons[$dt] ?? '🩺' }} {{ \App\Models\Doctor::DOCTOR_TYPES[$dt] ?? 'Doctor' }}
          </span>
        @endforeach
      </div>
    </div>
    @if($bestScore)
      <div style="text-align:center;">
        <div style="font-size:11px;color:rgba(255,255,255,0.35);margin-bottom:4px;">BEST SCORE</div>
        <div style="font-family:'Fraunces',serif;font-size:52px;font-weight:900;line-height:1;color:{{ $bestScore>=75?'#4ADE80':($bestScore>=55?'#FCD34D':'#EF4444') }};">{{ $bestScore }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,0.35);">/100</div>
      </div>
    @endif
  </div>

  {{-- ── SPECIALIST SECTIONS ── --}}
  @foreach($allCheckups as $c)
    @php
      $drType   = $c->doctor?->doctor_type;
      $drLabel  = \App\Models\Doctor::DOCTOR_TYPES[$drType] ?? 'Doctor';
      $drColor  = $typeColors[$drType] ?? '#6B7280';
      $drIcon   = $typeIcons[$drType] ?? '🩺';
      $sections = \App\Models\Doctor::DOCTOR_TYPE_SECTIONS[$drType] ?? [];
    @endphp

    <div class="card" style="margin-bottom:14px;border-top:3px solid {{ $drColor }};">
      {{-- Section header --}}
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:38px;height:38px;border-radius:10px;background:{{ $drColor }}18;border:1.5px solid {{ $drColor }}33;display:flex;align-items:center;justify-content:center;font-size:16px;">{{ $drIcon }}</div>
          <div>
            <div class="fw-700" style="font-size:15px;color:var(--dk);">{{ $drLabel }}</div>
            <div class="meta">Dr. {{ $c->doctor?->name ?? '—' }} · {{ $c->checkup_date->format('d M Y') }}</div>
          </div>
        </div>
        @if($c->overall_score)
          <div style="font-size:22px;font-weight:900;color:{{ $c->overall_score>=75?'var(--g)':($c->overall_score>=55?'var(--or)':'var(--r)') }};">
            {{ $c->overall_score }}<span style="font-size:11px;color:var(--gr);">/100</span>
          </div>
        @endif
      </div>

      {{-- ── Physical & Vitals ── --}}
      @if(in_array('physical', $sections))
        <div style="margin-bottom:14px;">
          <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Physical & Vitals</div>
          @php
            $physParams = [
              ['Height',         $c->height_cm,       200, 'cm',    100, 200],
              ['Weight',         $c->weight_kg,       150, 'kg',     20,  80],
              ['BMI',            $c->bmi,              35, '',      18.5,24.9],
              ['Heart Rate',     $c->heart_rate_bpm,  150, 'bpm',   60, 100],
              ['Temperature',    $c->temperature_f,   110, '°F',    97,  99],
              ['SpO2',           $c->spo2_percent,    100, '%',     95, 100],
            ];
          @endphp
          @foreach($physParams as [$lbl, $val, $max, $unit, $lo, $hi])
            @if($val !== null)
              @php
                $pct   = min(100, round(($val/$max)*100));
                $good  = $val >= $lo && $val <= $hi;
                $cc    = $good ? 'var(--g)' : (abs($val < $lo ? $lo-$val : $val-$hi) / ($lo ?: 1) > 0.25 ? 'var(--r)' : 'var(--or)');
                $badge = $good ? 'bg' : ($val < $lo ? 'br' : 'br');
                $text  = $good ? 'Normal' : ($val < $lo ? 'Low' : 'High');
              @endphp
              <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">{{ $lbl }}</div>
                <div style="flex:1;height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                  <div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div>
                </div>
                <div style="font-size:12px;font-weight:700;color:{{ $cc }};width:70px;text-align:right;">{{ $val }}{{ $unit }}</div>
                <span class="badge {{ $badge }}" style="width:52px;text-align:center;font-size:10px;">{{ $text }}</span>
              </div>
            @endif
          @endforeach
          @if($c->bp_systolic)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
              <div style="font-size:12px;color:var(--gr);">Blood Pressure</div>
              <div style="font-size:12px;font-weight:700;color:var(--dk);">{{ $c->bp_systolic }}/{{ $c->bp_diastolic }} mmHg</div>
            </div>
          @endif
        </div>
        {{-- Skin & Hair --}}
        @if($c->skin_health || $c->hair_health)
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Skin & Hair</div>
            @foreach([['Skin Health',$c->skin_health],['Hair & Scalp',$c->hair_health]] as [$lbl,$val])
              @if($val)
                @php $g=in_array($val,['Healthy']); $b=in_array($val,['Needs Attention']); @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                  <div style="font-size:12px;color:var(--gr);">{{ $lbl }}</div>
                  <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $val }}</span>
                </div>
              @endif
            @endforeach
          </div>
        @endif
      @endif

      {{-- ── Dental ── --}}
      @if(in_array('dental', $sections) && $c->dental_score !== null)
        <div style="margin-bottom:14px;">
          <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Dental Health</div>
          @php $v=$c->dental_score; $pct=round(($v/10)*100); $cc=$v>=7?'var(--g)':($v>=5?'var(--or)':'var(--r)'); $text=$v>=7?'Good':($v>=5?'Average':'Poor'); @endphp
          <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--lgr);">
            <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">Dental Score</div>
            <div style="flex:1;height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div></div>
            <div style="font-size:12px;font-weight:700;color:{{ $cc }};width:70px;text-align:right;">{{ $v }}/10</div>
            <span class="badge {{ $v>=7?'bg':($v>=5?'by':'br') }}" style="width:52px;text-align:center;font-size:10px;">{{ $text }}</span>
          </div>
        </div>
      @endif

      {{-- ── Eye / Vision ── --}}
      @if(in_array('eye', $sections))
        @if($c->vision_left || $c->vision_right || $c->eye_strain)
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Vision & Eye Health</div>
            @if($c->vision_left || $c->vision_right)
              <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);">Vision</div>
                <div style="font-size:12px;font-weight:700;color:var(--dk);">L: {{ $c->vision_left ?? '—' }} &nbsp;·&nbsp; R: {{ $c->vision_right ?? '—' }}</div>
              </div>
            @endif
            @if($c->eye_strain)
              @php $g=($c->eye_strain==='None'); $b=($c->eye_strain==='Severe'); @endphp
              <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);">Eye Strain</div>
                <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $c->eye_strain }}</span>
              </div>
            @endif
          </div>
        @endif
      @endif

      {{-- ── Hearing ── --}}
      @if(in_array('hearing', $sections) && $c->hearing)
        <div style="margin-bottom:14px;">
          <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Hearing</div>
          @php $g=($c->hearing==='Normal'); $b=($c->hearing==='Needs Test'); @endphp
          <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
            <div style="font-size:12px;color:var(--gr);">Hearing Assessment</div>
            <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $c->hearing }}</span>
          </div>
        </div>
      @endif

      {{-- ── Lab ── --}}
      @if(in_array('lab', $sections))
        @php
          $labParams = [
            ['Haemoglobin', $c->haemoglobin_gdl, 20, 'g/dL', ($student->gender==='F'?11.5:13), 17],
            ['Vitamin D',   $c->vitamin_d_ngml,  100,'ng/mL', 30, 80],
            ['Blood Sugar', $c->blood_sugar_mgdl, 400,'mg/dL', 70, 140],
          ];
          $hasLab = $c->haemoglobin_gdl || $c->vitamin_d_ngml || $c->blood_sugar_mgdl || $c->iron_level;
        @endphp
        @if($hasLab)
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Lab Results</div>
            @foreach($labParams as [$lbl,$val,$max,$unit,$lo,$hi])
              @if($val !== null)
                @php
                  $pct  = min(100, round(($val/$max)*100));
                  $good = $val >= $lo && $val <= $hi;
                  $cc   = $good ? 'var(--g)' : (abs($val < $lo ? $lo-$val : $val-$hi) / ($lo ?: 1) > 0.25 ? 'var(--r)' : 'var(--or)');
                  $text = $good ? 'Normal' : ($val < $lo ? 'Low' : 'High');
                @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--lgr);">
                  <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">{{ $lbl }}</div>
                  <div style="flex:1;height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div></div>
                  <div style="font-size:12px;font-weight:700;color:{{ $cc }};width:70px;text-align:right;">{{ $val }}{{ $unit }}</div>
                  <span class="badge {{ $good?'bg':($val<$lo?'br':'br') }}" style="width:52px;text-align:center;font-size:10px;">{{ $text }}</span>
                </div>
              @endif
            @endforeach
            @if($c->iron_level)
              @php $g=($c->iron_level==='Normal'); $b=($c->iron_level==='Very Low'); @endphp
              <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);">Iron Level</div>
                <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $c->iron_level }}</span>
              </div>
            @endif
          </div>
        @endif
      @endif

      {{-- ── Musculoskeletal ── --}}
      @if(in_array('musculoskeletal', $sections))
        @php $hasMsk = $c->posture || $c->flexibility || $c->flat_feet || $c->grip_strength_score; @endphp
        @if($hasMsk)
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Musculoskeletal</div>
            @if($c->grip_strength_score !== null)
              @php $v=$c->grip_strength_score; $pct=round(($v/10)*100); $cc=$v>=6?'var(--g)':($v>=4?'var(--or)':'var(--r)'); @endphp
              <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">Grip Strength</div>
                <div style="flex:1;height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div></div>
                <div style="font-size:12px;font-weight:700;color:{{ $cc }};width:70px;text-align:right;">{{ $v }}/10</div>
                <span class="badge {{ $v>=6?'bg':($v>=4?'by':'br') }}" style="width:52px;text-align:center;font-size:10px;">{{ $v>=6?'Good':($v>=4?'Avg':'Low') }}</span>
              </div>
            @endif
            @foreach([['Posture',$c->posture,['Good','Mild Curve','Scoliosis Risk']],['Flexibility',$c->flexibility,['Good','Average','Poor']],['Flat Feet',$c->flat_feet,['None','Mild','Moderate']]] as [$lbl,$val,$opts])
              @if($val)
                @php $g=($val===$opts[0]); $b=($val===$opts[2]); @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                  <div style="font-size:12px;color:var(--gr);">{{ $lbl }}</div>
                  <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $val }}</span>
                </div>
              @endif
            @endforeach
          </div>
        @endif
      @endif

      {{-- ── Mental / Wellness ── --}}
      @if(in_array('mental', $sections))
        @php $hasMental = $c->mental_score || $c->stress_level || $c->sleep_quality; @endphp
        @if($hasMental)
          <div style="margin-bottom:14px;">
            <div style="font-size:11px;font-weight:700;color:var(--gr);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Wellness & Mental Health</div>
            @if($c->mental_score !== null)
              @php $v=$c->mental_score; $pct=round(($v/10)*100); $cc=$v>=7?'var(--g)':($v>=5?'var(--or)':'var(--r)'); @endphp
              <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">Mental Well-being</div>
                <div style="flex:1;height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div></div>
                <div style="font-size:12px;font-weight:700;color:{{ $cc }};width:70px;text-align:right;">{{ $v }}/10</div>
                <span class="badge {{ $v>=7?'bg':($v>=5?'by':'br') }}" style="width:52px;text-align:center;font-size:10px;">{{ $v>=7?'Good':($v>=5?'Avg':'Low') }}</span>
              </div>
            @endif
            @foreach([['Stress Level',$c->stress_level,['Low','Moderate','High']],['Sleep Quality',$c->sleep_quality,['Good','Average','Poor']]] as [$lbl,$val,$opts])
              @if($val)
                @php $g=($val===$opts[0]); $b=($val===$opts[2]); @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);">
                  <div style="font-size:12px;color:var(--gr);">{{ $lbl }}</div>
                  <span class="badge {{ $g?'bg':($b?'br':'by') }}">{{ $val }}</span>
                </div>
              @endif
            @endforeach
          </div>
        @endif
      @endif

      {{-- ── Alerts for this checkup ── --}}
      @if(count($c->alerts ?? []) > 0)
        <div style="margin-bottom:10px;">
          @foreach($c->alerts as $alert)
            <div class="alert alert-r" style="margin-bottom:6px;font-size:12px;padding:8px 12px;">⚠️ {{ $alert }}</div>
          @endforeach
        </div>
      @endif

      {{-- ── Doctor notes ── --}}
      @if($c->doctor_notes || $c->recommendations)
        <div style="background:var(--lgr);border-radius:10px;padding:12px 14px;margin-top:4px;">
          @if($c->doctor_notes)
            <div style="font-size:11px;font-weight:700;color:var(--gr);margin-bottom:4px;">DOCTOR'S OBSERVATIONS</div>
            <div style="font-size:13px;color:var(--dk);line-height:1.6;margin-bottom:8px;">{{ $c->doctor_notes }}</div>
          @endif
          @if($c->recommendations)
            <div style="font-size:11px;font-weight:700;color:var(--g);margin-bottom:4px;">RECOMMENDATIONS</div>
            <div style="font-size:13px;color:var(--dk);line-height:1.6;">{{ $c->recommendations }}</div>
          @endif
        </div>
      @endif

    </div>
  @endforeach

  {{-- ── COMBINED ALERTS SUMMARY ── --}}
  @if($allAlerts->count() > 0)
    <div class="card" style="border-top:3px solid var(--r);">
      <div class="card-header"><div class="card-title">⚠️ All Health Alerts</div><span class="badge br">{{ $allAlerts->count() }}</span></div>
      @foreach($allAlerts as $alert)
        <div class="alert alert-r" style="margin-bottom:6px;font-size:13px;">⚠️ <strong>{{ $alert }}</strong></div>
      @endforeach
      <div style="margin-top:10px;font-size:12px;color:var(--gr);">
        Contact us for doctor referrals: <a href="mailto:info@checkmy.fitness" style="color:var(--g);">info@checkmy.fitness</a>
      </div>
    </div>
  @else
    <div class="card"><div class="alert alert-g">✅ No critical alerts across any specialist checkup. {{ $student->name }} is doing well!</div></div>
  @endif

@endif
@endsection
