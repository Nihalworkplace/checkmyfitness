{{-- resources/views/parent/report.blade.php --}}
@extends('layouts.app')
@section('title','Health Report — '.$student->name)
@section('page-title','Health Report')
@section('sidebar-nav')
<a href="{{ route('parent.dashboard') }}" class="ni"><div class="ni-ico" style="background:rgba(29,158,117,0.2);">🏠</div> Overview</a>
<a href="{{ route('parent.report', $student) }}" class="ni active" style="background:rgba(29,158,117,0.15);color:#fff;"><div class="ni-ico" style="background:rgba(29,158,117,0.3);">📋</div> Health Report</a>
<a href="{{ route('parent.timeline', $student) }}" class="ni"><div class="ni-ico" style="background:rgba(59,130,246,0.2);">📈</div> Timeline</a>
<a href="{{ route('parent.rewards', $student) }}" class="ni"><div class="ni-ico" style="background:rgba(245,158,11,0.2);">🎁</div> Rewards</a>
@endsection

@section('content')
@if(!$latestCheckup)
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">🩺</div>
    <div style="font-size:16px;font-weight:700;color:var(--gr);">No checkup completed yet for {{ $student->name }}.</div>
    <div style="font-size:13px;color:var(--bd);margin-top:8px;">Checkup reports will appear here after the doctor's visit.</div>
  </div>
@else
  @php $c = $latestCheckup; @endphp

  {{-- Score header --}}
  <div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="width:50px;height:50px;border-radius:14px;background:{{ $student->gender==='M'?'#3B82F6':'#8B5CF6' }};display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0;">{{ strtoupper(substr($student->name,0,1)) }}</div>
    <div style="flex:1;">
      <div style="font-family:'Fraunces',serif;font-size:20px;font-weight:900;color:#fff;">{{ $student->name }}'s Health Report</div>
      <div style="font-size:12px;color:rgba(255,255,255,0.4);">{{ $c->checkup_date->format('d F Y') }} · Dr. {{ $c->doctor->name }} · {{ $student->school_name }}</div>
    </div>
    <div>
      <div style="font-family:'Fraunces',serif;font-size:52px;font-weight:900;color:{{ $c->overall_score>=75?'#4ADE80':($c->overall_score>=55?'#FCD34D':'#EF4444') }};">{{ $c->overall_score }}</div>
      <div style="font-size:11px;color:rgba(255,255,255,0.35);text-align:right;">/100 overall</div>
    </div>
  </div>

  <div class="g2">
    {{-- All parameters --}}
    <div class="card">
      <div class="card-header"><div class="card-title">📊 All Parameters</div></div>
      @php
        $paramDisplay = [
          ['Haemoglobin', $c->haemoglobin_gdl, 18, 'g/dL', 11.5, 17],
          ['Vitamin D',   $c->vitamin_d_ngml,  80, 'ng/mL', 30, 80],
          ['Dental Score',$c->dental_score,    10, '/10',   7, 10],
          ['Mental Score',$c->mental_score,    10, '/10',   7, 10],
          ['BMI',         $c->bmi,             35, '',      18.5, 24.9],
          ['Heart Rate',  $c->heart_rate_bpm, 140, 'bpm',  60, 100],
          ['SpO2',        $c->spo2_percent,   100, '%',    95, 100],
          ['Vitamin D',   $c->vitamin_d_ngml,  80, 'ng/mL', 30, 80],
          ['Blood Sugar', $c->blood_sugar_mgdl,400,'mg/dL', 70, 140],
          ['Grip Strength',$c->grip_strength_score,10,'/10',6, 10],
        ];
      @endphp
      @foreach(array_unique($paramDisplay, SORT_REGULAR) as [$label, $val, $max, $unit, $lo, $hi])
        @if($val !== null)
          @php
            $pct = min(round(($val/$max)*100), 100);
            $good = $val >= $lo && $val <= $hi;
            $c_color = $good ? 'var(--g)' : (abs($val - ($val<$lo?$lo:$hi))/($lo ?: 1) > 0.25 ? 'var(--r)' : 'var(--or)');
            $status = $good ? 'Good' : ($val < $lo ? 'Low' : 'High');
          @endphp
          <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--lgr);">
            <div style="font-size:12px;color:var(--gr);width:115px;flex-shrink:0;">{{ $label }}</div>
            <div style="flex:1;height:7px;background:var(--lgr);border-radius:4px;overflow:hidden;">
              <div style="width:{{ $pct }}%;height:100%;background:{{ $c_color }};border-radius:4px;"></div>
            </div>
            <div style="font-size:12px;font-weight:700;color:{{ $c_color }};width:64px;text-align:right;">{{ $val }}{{ $unit }}</div>
            <span class="badge {{ $good?'bg':($val<$lo?'br':'br') }}" style="width:48px;text-align:center;">{{ $status }}</span>
          </div>
        @endif
      @endforeach

      {{-- Quick params --}}
      @foreach([
        ['Hearing',$c->hearing], ['Eye Strain',$c->eye_strain], ['Iron Level',$c->iron_level],
        ['Posture',$c->posture], ['Flexibility',$c->flexibility], ['Flat Feet',$c->flat_feet],
        ['Stress Level',$c->stress_level], ['Sleep Quality',$c->sleep_quality],
        ['Skin Health',$c->skin_health], ['Hair Health',$c->hair_health],
      ] as [$label,$val])
        @if($val)
          @php
            $good2 = in_array($val, ['Normal','None','Good','Healthy','Low']); // Low stress = good
            $bad2  = in_array($val, ['Severe','Scoliosis Risk','Very Low','High','Needs Attention','Needs Test']);
          @endphp
          <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--lgr);">
            <div style="font-size:12px;color:var(--gr);">{{ $label }}</div>
            <span class="badge {{ $good2?'bg':($bad2?'br':'by') }}">{{ $val }}</span>
          </div>
        @endif
      @endforeach
    </div>

    <div style="display:flex;flex-direction:column;gap:14px;">
      {{-- Measurements --}}
      <div class="card">
        <div class="card-header"><div class="card-title">📏 Measurements</div></div>
        @foreach([
          ['Height', $c->height_cm ? $c->height_cm.' cm' : null],
          ['Weight', $c->weight_kg ? $c->weight_kg.' kg' : null],
          ['BMI',    $c->bmi ? $c->bmi.' (Normal: 18.5–24.9)' : null],
          ['Blood Pressure', $c->bp_systolic ? $c->bp_systolic.'/'.$c->bp_diastolic.' mmHg' : null],
          ['Temperature', $c->temperature_f ? $c->temperature_f.'°F' : null],
          ['SpO2', $c->spo2_percent ? $c->spo2_percent.'%' : null],
          ['Haemoglobin', $c->haemoglobin_gdl ? $c->haemoglobin_gdl.' g/dL' : null],
          ['Blood Sugar', $c->blood_sugar_mgdl ? $c->blood_sugar_mgdl.' mg/dL' : null],
          ['Vision', $c->vision_left ? 'L:'.$c->vision_left.' R:'.$c->vision_right : null],
        ] as [$label,$val])
          @if($val)
            <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--lgr);font-size:13px;">
              <span style="color:var(--gr);">{{ $label }}</span>
              <span style="font-weight:600;">{{ $val }}</span>
            </div>
          @endif
        @endforeach
      </div>

      {{-- Alerts --}}
      @if(count($c->alerts??[])>0)
        <div class="card">
          <div class="card-header"><div class="card-title">⚠️ Alerts & Actions</div></div>
          @foreach($c->alerts as $alert)
            <div class="alert alert-r" style="margin-bottom:8px;">⚠️ <strong>{{ $alert }}</strong></div>
          @endforeach
          <div style="margin-top:10px;font-size:12px;color:var(--gr);">Contact us for doctor referrals: <a href="mailto:info@checkmy.fitness" style="color:var(--g);">info@checkmy.fitness</a> · +91 79902 08857</div>
        </div>
      @else
        <div class="card"><div class="alert alert-g">✅ No critical alerts. {{ $student->name }} is doing well!</div></div>
      @endif

      {{-- Doctor notes --}}
      @if($c->doctor_notes || $c->recommendations)
        <div class="card">
          <div class="card-header"><div class="card-title">👨‍⚕️ Doctor's Notes</div></div>
          @if($c->doctor_notes)
            <div style="font-size:13px;color:var(--dk);line-height:1.6;margin-bottom:12px;">{{ $c->doctor_notes }}</div>
          @endif
          @if($c->recommendations)
            <div style="background:var(--lgr);border-radius:10px;padding:14px;">
              <div style="font-size:11px;font-weight:700;color:var(--gr);margin-bottom:6px;">RECOMMENDATIONS</div>
              <div style="font-size:13px;color:var(--dk);line-height:1.6;">{{ $c->recommendations }}</div>
            </div>
          @endif
        </div>
      @endif
    </div>
  </div>
@endif
@endsection
