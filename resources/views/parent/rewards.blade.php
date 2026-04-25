@extends('layouts.app')
@section('title','Rewards')
@section('page-title','Reward Store')
@section('sidebar-nav')
@include('parent.partials.nav')
@endsection

@section('content')
@php
  $latestCheckup = $student->latestCheckup();
  $coins = $latestCheckup ? ($latestCheckup->overall_score * 5) : 0; // Demo: 5 coins per score point
  $rewards = [
    ['ico'=>'📚','name'=>'Stationery Kit','cost'=>200,'category'=>'School'],
    ['ico'=>'♟️','name'=>'Chess Set','cost'=>400,'category'=>'Games'],
    ['ico'=>'🏅','name'=>'Sports Badge','cost'=>150,'category'=>'Achievement'],
    ['ico'=>'🏆','name'=>'Health Champion Certificate','cost'=>100,'category'=>'Achievement'],
    ['ico'=>'☕','name'=>'Canteen Voucher (₹50)','cost'=>180,'category'=>'Food'],
    ['ico'=>'📖','name'=>'Library Priority Pass','cost'=>120,'category'=>'School'],
    ['ico'=>'🎯','name'=>'Sports Equipment','cost'=>350,'category'=>'Sports'],
    ['ico'=>'🎮','name'=>'Board Game','cost'=>500,'category'=>'Games'],
  ];
@endphp

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:18px;">
  <div class="scard">
    <div class="sc-l">🪙 Coin Balance</div>
    <div class="sc-v" style="color:var(--or);">{{ $coins }}</div>
    <div class="sc-s" style="color:var(--gr);">Earned by improving health</div>
  </div>
  <div class="scard">
    <div class="sc-l">Rewards Unlocked</div>
    <div class="sc-v">{{ count(array_filter($rewards, fn($r) => $r['cost'] <= $coins)) }}</div>
    <div class="sc-s" style="color:var(--g);">Available to redeem</div>
  </div>
  <div class="scard">
    <div class="sc-l">Overall Health Score</div>
    <div class="sc-v" style="color:{{ $latestCheckup && $latestCheckup->overall_score>=75?'var(--g)':($latestCheckup && $latestCheckup->overall_score>=55?'var(--or)':'var(--r)') }};">{{ $latestCheckup?->overall_score ?? '—' }}</div>
    <div class="sc-s" style="color:var(--gr);">Higher score = more coins</div>
  </div>
</div>

<div class="g2">
  <div class="card">
    <div class="card-header"><div class="card-title">🎁 Reward Store</div></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      @foreach($rewards as $reward)
        @php $unlocked = $reward['cost'] <= $coins; @endphp
        <div style="border-radius:12px;padding:14px;text-align:center;position:relative;border:1.5px solid {{ $unlocked?'var(--g)':'var(--bd)' }};background:{{ $unlocked?'#F0FDF9':'#fff' }};transition:all .2s;{{ !$unlocked?'opacity:.6':'' }}">
          @if($unlocked)
            <div style="position:absolute;top:6px;right:6px;font-size:9px;font-weight:700;padding:2px 6px;border-radius:5px;background:var(--g);color:#fff;">Unlocked</div>
          @endif
          <div style="font-size:26px;margin-bottom:7px;">{{ $reward['ico'] }}</div>
          <div style="font-size:12px;font-weight:700;color:var(--dk);margin-bottom:3px;">{{ $reward['name'] }}</div>
          <div style="font-size:10px;color:var(--or);margin-bottom:10px;">🪙 {{ $reward['cost'] }} coins</div>
          @if($unlocked)
            <button onclick="alert('Redeemed! Show this screen to your school admin.')" style="width:100%;background:var(--g);color:#fff;border:none;border-radius:8px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Redeem</button>
          @else
            <div style="font-size:10px;color:var(--gr);">Need {{ $reward['cost'] - $coins }} more coins</div>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-title">How to Earn More Coins</div></div>
    <div style="margin-bottom:16px;background:linear-gradient(135deg,var(--g),var(--g2));border-radius:12px;padding:16px;color:#fff;">
      <div style="font-size:10px;font-weight:700;opacity:.6;letter-spacing:1px;margin-bottom:6px;">💡 PERSONALISED FOR {{ strtoupper($student->name) }}</div>
      <div style="font-size:14px;font-weight:700;margin-bottom:6px;">Focus on these to earn more coins:</div>
      @if($latestCheckup)
        <div style="font-size:12px;opacity:.82;line-height:1.6;">
          @if($latestCheckup->haemoglobin_gdl && $latestCheckup->haemoglobin_gdl < 11.5)• Improve haemoglobin → +50 coins<br/>@endif
          @if($latestCheckup->dental_score && $latestCheckup->dental_score < 7)• Improve dental score → +30 coins<br/>@endif
          @if($latestCheckup->vitamin_d_ngml && $latestCheckup->vitamin_d_ngml < 30)• Normalise Vitamin D → +30 coins<br/>@endif
          @if($latestCheckup->mental_score && $latestCheckup->mental_score < 7)• Improve mental well-being → +40 coins<br/>@endif
          • Overall score reaches 85+ → +100 coins
        </div>
      @endif
    </div>
    @foreach([
      ['🩸','Haemoglobin improves to normal','+50 coins'],
      ['🦷','Dental score reaches 7/10','+30 coins'],
      ['☀️','Vitamin D normalises','+30 coins'],
      ['🧠','Mental score 8/10','+40 coins'],
      ['✅','Checkup completed on time','+20 coins'],
      ['⚖️','BMI stays in healthy range','+25 coins'],
      ['💪','Strength improves to 8/10','+25 coins'],
      ['📈','Overall score reaches 85+','+100 coins'],
    ] as [$ico,$label,$coins_str])
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--lgr);">
        <span style="font-size:18px;">{{ $ico }}</span>
        <div style="flex:1;font-size:13px;color:var(--dk);">{{ $label }}</div>
        <div style="font-size:13px;font-weight:700;color:var(--or);">{{ $coins_str }}</div>
      </div>
    @endforeach
  </div>
</div>
@endsection
