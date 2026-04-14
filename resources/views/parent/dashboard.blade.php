@extends('layouts.app')
@section('title','My Child\'s Health')
@section('page-title','Health Dashboard')

@section('sidebar-nav')
@if($students->isNotEmpty())
  @foreach($students as $stu)
  <div class="nb-label">{{ $stu->name }}</div>
  <a href="{{ route('parent.dashboard') }}" class="ni {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <div class="ni-ico" style="background:rgba(29,158,117,0.2);">🏠</div> Overview
  </a>
  <a href="{{ route('parent.report', $stu) }}" class="ni {{ request()->routeIs('parent.report') ? 'active' : '' }}">
    <div class="ni-ico" style="background:rgba(29,158,117,0.2);">📋</div> Health Report
  </a>
  <a href="{{ route('parent.timeline', $stu) }}" class="ni">
    <div class="ni-ico" style="background:rgba(59,130,246,0.2);">📈</div> Timeline
  </a>
  <a href="{{ route('parent.rewards', $stu) }}" class="ni">
    <div class="ni-ico" style="background:rgba(245,158,11,0.2);">🎁</div> Rewards
  </a>
  @endforeach
@endif
@endsection

@section('content')
@if($students->isEmpty())
  <div class="card" style="text-align:center;padding:48px;">
    <div style="font-size:48px;margin-bottom:16px;">👶</div>
    <div style="font-size:18px;font-weight:700;color:var(--gr);">No students linked to your account yet.</div>
    <div style="font-size:14px;color:var(--bd);margin-top:8px;">Contact CheckMyFitness admin: <a href="mailto:info@checkmy.fitness" style="color:var(--g);">info@checkmy.fitness</a></div>
  </div>
@else
  @foreach($students as $stu)
    @php $checkup = $stu->latestCheckup(); @endphp
    <div style="background:var(--dk);border-radius:18px;padding:22px;margin-bottom:18px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
      <div style="width:52px;height:52px;border-radius:14px;background:{{ $stu->gender==='M'?'#3B82F6':'#8B5CF6' }};display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0;">{{ strtoupper(substr($stu->name,0,1)) }}</div>
      <div style="flex:1;">
        <div style="font-family:'Fraunces',serif;font-size:22px;font-weight:900;color:#fff;">{{ $stu->name }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,0.4);">Class {{ $stu->class_section }} · {{ $stu->school_name }} · Age {{ $stu->age }} · Ref: {{ $stu->reference_code }}</div>
      </div>
      @if($checkup)
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Overall Score</div>
          <div style="font-family:'Fraunces',serif;font-size:44px;font-weight:900;color:{{ $checkup->overall_score>=75?'#4ADE80':($checkup->overall_score>=55?'#FCD34D':'#EF4444') }};">{{ $checkup->overall_score }}</div>
          <div style="font-size:11px;color:rgba(255,255,255,0.35);">{{ $checkup->checkup_date->format('M Y') }}</div>
        </div>
      @else
        <div style="font-size:13px;color:rgba(255,255,255,0.35);">No checkup yet</div>
      @endif
    </div>

    @if($checkup)
      <div class="g2" style="margin-bottom:18px;">
        <div class="card">
          <div class="card-header"><div class="card-title">Health Parameters</div><a href="{{ route('parent.report', $stu) }}" class="btn btn-sm btn-out">Full Report →</a></div>
          @foreach(['haemoglobin_gdl'=>['Haemoglobin',18,' g/dL'],'vitamin_d_ngml'=>['Vitamin D',80,' ng/mL'],'dental_score'=>['Dental',10,' /10'],'mental_score'=>['Mental Health',10,' /10'],'grip_strength_score'=>['Strength',10,' /10']] as $field=>[$label,$max,$unit])
            @if($checkup->$field)
              @php $pct=round(($checkup->$field/$max)*100); $c=$pct>=72?'var(--g)':($pct>=50?'var(--or)':'var(--r)'); @endphp
              <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--lgr);">
                <div style="font-size:12px;color:var(--gr);width:110px;flex-shrink:0;">{{ $label }}</div>
                <div style="flex:1;height:7px;background:var(--lgr);border-radius:4px;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:{{ $c }};border-radius:4px;"></div></div>
                <div style="font-size:12px;font-weight:700;color:{{ $c }};width:50px;text-align:right;">{{ $checkup->$field }}{{ $unit }}</div>
              </div>
            @endif
          @endforeach
        </div>
        <div style="display:flex;flex-direction:column;gap:14px;">
          @if(count($checkup->alerts ?? []) > 0)
            <div class="card">
              <div class="card-header"><div class="card-title">⚠️ Health Alerts</div></div>
              @foreach($checkup->alerts as $alert)
                <div class="alert alert-r" style="margin-bottom:6px;font-size:13px;">⚠️ {{ $alert }}</div>
              @endforeach
              <div style="margin-top:10px;font-size:12px;color:var(--gr);">Please consult your family doctor or contact CheckMyFitness for referrals.</div>
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
