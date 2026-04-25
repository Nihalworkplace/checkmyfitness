@extends('layouts.app')
@section('title', "My Child's Health")
@section('page-title', 'Health Dashboard')

@section('sidebar-nav')
@include('parent.partials.nav')
@endsection

@section('content')

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
  'general_physician' => '🩺',
  'dentist'           => '🦷',
  'eye_specialist'    => '👁️',
  'audiologist_ent'   => '👂',
  'physiotherapist'   => '🦴',
  'psychologist'      => '🧠',
  'lab_technician'    => '🧪',
];
@endphp

@if($students->isEmpty())
  <div class="card empty-state--lg text-center">
    <div style="font-size:48px;margin-bottom:16px;">👶</div>
    <div style="font-size:18px;font-weight:700;" class="text-muted">No students linked to your account yet.</div>
    <div class="fs-13 text-muted mt-8">
      Contact CheckMyFitness admin: <a href="mailto:info@checkmy.fitness" class="text-green">info@checkmy.fitness</a>
    </div>
  </div>

@else
  @foreach($students as $stu)
    @php
      // All completed checkups for this student, latest first
      $allCheckups = $stu->checkups->where('status', 'completed')->sortByDesc('checkup_date');
      $latestCheckup = $allCheckups->first();
      // All alerts from all checkups
      $allAlerts = $allCheckups->flatMap(fn($c) => $c->alerts ?? [])->values();
    @endphp

    {{-- Student header --}}
    <div class="page-header mb-18">
      <div class="page-header__left">
        <div class="avatar avatar--lg {{ $stu->gender === 'M' ? 'avatar--male' : 'avatar--female' }}">
          {{ strtoupper(substr($stu->name, 0, 1)) }}
        </div>
        <div class="page-header__body">
          <div class="page-header__title">{{ $stu->name }}</div>
          <div class="page-header__sub-sm">
            Class {{ $stu->class_section }} · {{ $stu->school_name }} · Age {{ $stu->age }} · Ref: {{ $stu->reference_code }}
          </div>
        </div>
      </div>
      @if($latestCheckup)
        <div class="text-center">
          <div class="meta" style="margin-bottom:4px;">Overall Score</div>
          <div style="font-family:var(--ff);font-size:44px;font-weight:900;color:{{ $latestCheckup->overall_score>=75?'#4ADE80':($latestCheckup->overall_score>=55?'#FCD34D':'#EF4444') }};">{{ $latestCheckup->overall_score }}</div>
          <div class="meta">{{ $latestCheckup->checkup_date->format('M Y') }}</div>
        </div>
      @else
        <div class="meta">No checkup yet</div>
      @endif
    </div>

    @if($allCheckups->isNotEmpty())

      {{-- Alerts + quick actions --}}
      <div class="g2 mb-18">

        {{-- All specialist checkups summary --}}
        <div class="card">
          <div class="card-header">
            <div class="card-title">Health by Specialist</div>
            <a href="{{ route('parent.report', $stu) }}" class="btn btn-sm btn-out">Full Report →</a>
          </div>
          @foreach($allCheckups as $c)
            @php
              $drType  = $c->doctor?->doctor_type;
              $drLabel = $drType ? (\App\Models\Doctor::DOCTOR_TYPES[$drType] ?? 'Doctor') : 'Doctor';
              $drColor = $typeColors[$drType] ?? '#6B7280';
              $drIcon  = $typeIcons[$drType] ?? '🩺';
              $sections = \App\Models\Doctor::DOCTOR_TYPE_SECTIONS[$drType] ?? [];
            @endphp
            <div style="padding:10px 0;border-bottom:1px solid var(--lgr);">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="width:32px;height:32px;border-radius:9px;background:{{ $drColor }}18;border:1px solid {{ $drColor }}33;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">{{ $drIcon }}</div>
                  <div>
                    <div class="fw-700 fs-13" style="color:var(--dk);">{{ $drLabel }}</div>
                    <div class="meta">{{ $c->checkup_date->format('d M Y') }} · Dr. {{ $c->doctor?->name ?? '—' }}</div>
                  </div>
                </div>
                @if($c->overall_score)
                  <div style="font-size:18px;font-weight:900;color:{{ $c->overall_score>=75?'#4ADE80':($c->overall_score>=55?'#FCD34D':'#EF4444') }};">{{ $c->overall_score }}<span style="font-size:10px;color:var(--gr);">/100</span></div>
                @endif
              </div>

              {{-- Key params for this specialist --}}
              <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:10px;">
                @if(in_array('physical', $sections))
                  @if($c->height_cm)
                    <div class="list-row" style="padding:0;border:none;gap:6px;">
                      <div class="meta" style="width:90px;flex-shrink:0;">Height/Weight</div>
                      <div class="flex-auto" style="height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                        <div style="width:{{ min(100, round(($c->weight_kg??40)/80*100)) }}%;height:100%;background:var(--g);border-radius:3px;"></div>
                      </div>
                      <div class="fw-700 fs-11">{{ $c->height_cm }}cm · {{ $c->weight_kg }}kg{{ $c->bmi ? ' · BMI '.$c->bmi : '' }}</div>
                    </div>
                  @endif
                @endif
                @if(in_array('dental', $sections) && $c->dental_score)
                  @php $pct = round(($c->dental_score/10)*100); $cc = $pct>=70?'var(--g)':($pct>=50?'var(--or)':'var(--r)'); @endphp
                  <div class="list-row" style="padding:0;border:none;gap:6px;width:100%;">
                    <div class="meta" style="width:90px;flex-shrink:0;">Dental</div>
                    <div class="flex-auto" style="height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                      <div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div>
                    </div>
                    <div class="fw-700 fs-11" style="width:50px;text-align:right;color:{{ $cc }};">{{ $c->dental_score }}/10</div>
                  </div>
                @endif
                @if(in_array('eye', $sections) && ($c->vision_left || $c->vision_right))
                  <div style="font-size:12px;color:var(--dk);">👁️ L: {{ $c->vision_left ?? '—' }} · R: {{ $c->vision_right ?? '—' }}{{ $c->eye_strain ? ' · Strain: '.$c->eye_strain : '' }}</div>
                @endif
                @if(in_array('hearing', $sections) && $c->hearing)
                  <div style="font-size:12px;color:var(--dk);">👂 Hearing: {{ $c->hearing }}</div>
                @endif
                @if(in_array('musculoskeletal', $sections) && $c->grip_strength_score)
                  @php $pct = round(($c->grip_strength_score/10)*100); $cc = $pct>=60?'var(--g)':($pct>=40?'var(--or)':'var(--r)'); @endphp
                  <div class="list-row" style="padding:0;border:none;gap:6px;width:100%;">
                    <div class="meta" style="width:90px;flex-shrink:0;">Grip Strength</div>
                    <div class="flex-auto" style="height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                      <div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div>
                    </div>
                    <div class="fw-700 fs-11" style="width:50px;text-align:right;color:{{ $cc }};">{{ $c->grip_strength_score }}/10</div>
                  </div>
                @endif
                @if(in_array('mental', $sections) && $c->mental_score)
                  @php $pct = round(($c->mental_score/10)*100); $cc = $pct>=70?'var(--g)':($pct>=50?'var(--or)':'var(--r)'); @endphp
                  <div class="list-row" style="padding:0;border:none;gap:6px;width:100%;">
                    <div class="meta" style="width:90px;flex-shrink:0;">Mental</div>
                    <div class="flex-auto" style="height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                      <div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div>
                    </div>
                    <div class="fw-700 fs-11" style="width:50px;text-align:right;color:{{ $cc }};">{{ $c->mental_score }}/10</div>
                  </div>
                @endif
                @if(in_array('lab', $sections) && $c->haemoglobin_gdl)
                  @php $lo = $stu->gender==='F'?11.5:13; $pct = min(100,round(($c->haemoglobin_gdl/$lo)*70)); $cc = $c->haemoglobin_gdl>=$lo?'var(--g)':'var(--r)'; @endphp
                  <div class="list-row" style="padding:0;border:none;gap:6px;width:100%;">
                    <div class="meta" style="width:90px;flex-shrink:0;">Haemoglobin</div>
                    <div class="flex-auto" style="height:6px;background:var(--lgr);border-radius:3px;overflow:hidden;">
                      <div style="width:{{ $pct }}%;height:100%;background:{{ $cc }};border-radius:3px;"></div>
                    </div>
                    <div class="fw-700 fs-11" style="width:60px;text-align:right;color:{{ $cc }};">{{ $c->haemoglobin_gdl }} g/dL</div>
                  </div>
                @endif
              </div>

              {{-- Specialist notes/recommendations --}}
              @if($c->doctor_notes || $c->recommendations)
                <div style="margin-top:8px;padding:8px 10px;background:var(--lgr);border-radius:8px;">
                  @if($c->doctor_notes)
                    <div class="doctor-note-italic" style="font-size:12px;">"{{ $c->doctor_notes }}"</div>
                  @endif
                  @if($c->recommendations)
                    <div style="font-size:12px;color:var(--g);margin-top:4px;"><strong>Recommendation:</strong> {{ $c->recommendations }}</div>
                  @endif
                </div>
              @endif
            </div>
          @endforeach
        </div>

        {{-- Alerts + quick actions --}}
        <div style="display:flex;flex-direction:column;gap:14px;">
          @if($allAlerts->count() > 0)
            <div class="card">
              <div class="card-header"><div class="card-title">⚠️ Health Alerts</div></div>
              @foreach($allAlerts as $alert)
                <div class="alert alert-r mb-0" style="margin-bottom:6px;font-size:13px;">⚠️ {{ $alert }}</div>
              @endforeach
              <div class="meta" style="margin-top:10px;">Please consult your family doctor or contact CheckMyFitness for referrals.</div>
            </div>
          @else
            <div class="card"><div class="alert alert-g">✅ No critical alerts. Keep up the good work!</div></div>
          @endif

          <div class="card">
            <div class="card-header"><div class="card-title">Quick Actions</div></div>
            <a href="{{ route('parent.report', $stu) }}" class="btn btn-g btn-full" style="margin-bottom:8px;">View Full Report →</a>
            <a href="{{ route('parent.timeline', $stu) }}" class="btn btn-out btn-full" style="margin-bottom:8px;">Health Timeline →</a>
            <a href="{{ route('parent.rewards', $stu) }}" class="btn btn-out btn-full">🎁 Reward Store</a>
          </div>
        </div>
      </div>

    @endif

  @endforeach
@endif

@endsection
