{{-- resources/views/parent/timeline.blade.php --}}
@extends('layouts.app')
@section('title','Health Timeline')
@section('page-title','Health Timeline')
@section('sidebar-nav')
@include('parent.partials.nav')
@endsection

@section('content')
<div class="card" style="margin-bottom:18px;">
  <div class="card-header"><div class="card-title">📈 {{ $student->name }}'s Health Journey — Class 1 to Class 12</div></div>
  @if($checkups->count() >= 2)
    {{-- SVG Line Chart --}}
    @php
      $pts = $checkups->reverse()->values();
      $count = $pts->count();
      $svgW = 560; $svgH = 140;
      $padL = 35; $padR = 20; $padT = 15; $padB = 30;
      $chartW = $svgW - $padL - $padR;
      $chartH = $svgH - $padT - $padB;
      $coords = $pts->map(function($c, $i) use ($count, $padL, $padR, $padT, $chartW, $chartH, $svgH) {
        $x = $padL + ($count <= 1 ? $chartW/2 : ($i / ($count-1)) * $chartW);
        $y = $padT + $chartH - ($c->overall_score / 100 * $chartH);
        return ['x'=>round($x,1), 'y'=>round($y,1), 'score'=>$c->overall_score, 'date'=>$c->checkup_date->format("M'y")];
      });
      $polyline = $coords->map(fn($p) => "{$p['x']},{$p['y']}")->join(' ');
    @endphp
    <div style="background:var(--lgr);border-radius:12px;padding:12px;overflow-x:auto;">
      <svg width="100%" viewBox="0 0 {{ $svgW }} {{ $svgH }}" xmlns="http://www.w3.org/2000/svg" style="display:block;min-width:320px;">
        {{-- Grid lines --}}
        @foreach([25,50,75,100] as $pct)
          @php $gy = $padT + $chartH - ($pct/100 * $chartH); @endphp
          <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $svgW-$padR }}" y2="{{ $gy }}" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="{{ $pct===50?'':'4'}}"/>
          <text x="{{ $padL-4 }}" y="{{ $gy+4 }}" font-size="9" fill="#94A3B8" text-anchor="end">{{ $pct }}</text>
        @endforeach
        {{-- Line --}}
        <polyline points="{{ $polyline }}" fill="none" stroke="#1D9E75" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        {{-- Area fill --}}
        <polyline points="{{ $coords->first()['x'] }},{{ $padT+$chartH }} {{ $polyline }} {{ $coords->last()['x'] }},{{ $padT+$chartH }}" fill="rgba(29,158,117,0.08)"/>
        {{-- Points --}}
        @foreach($coords as $pt)
          @php $ptc = $pt['score']>=75?'#1D9E75':($pt['score']>=55?'#F59E0B':'#EF4444'); @endphp
          <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="5" fill="{{ $ptc }}" stroke="white" stroke-width="2"/>
          <text x="{{ $pt['x'] }}" y="{{ $pt['y']-10 }}" font-size="9" fill="{{ $ptc }}" text-anchor="middle" font-weight="bold">{{ $pt['score'] }}</text>
          <text x="{{ $pt['x'] }}" y="{{ $padT+$chartH+14 }}" font-size="9" fill="#94A3B8" text-anchor="middle">{{ $pt['date'] }}</text>
        @endforeach
      </svg>
    </div>
  @else
    <div style="text-align:center;padding:24px;color:var(--gr);font-size:14px;">Need at least 2 checkups to show a timeline.</div>
  @endif
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Checkup History</div></div>
  @forelse($checkups as $i => $c)
    <div style="display:flex;gap:14px;padding-bottom:20px;position:relative;">
      @if(!$loop->last)
        <div style="position:absolute;left:15px;top:32px;bottom:0;width:2px;background:var(--bd);"></div>
      @endif
      <div style="width:32px;height:32px;border-radius:50%;border:2px solid {{ $c->overall_score>=75?'var(--g)':($c->overall_score>=55?'var(--or)':'var(--r)') }};background:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:{{ $c->overall_score>=75?'var(--g)':($c->overall_score>=55?'var(--or)':'var(--r)') }};flex-shrink:0;">{{ $checkups->count()-$i }}</div>
      <div style="flex:1;">
        <div style="font-size:11px;color:var(--gr);margin-bottom:2px;">{{ $c->checkup_date->format('d F Y') }}{{ $i===0?' · Most recent':'' }}</div>
        <div style="font-size:14px;font-weight:700;color:var(--dk);">Score: <span style="font-family:'Fraunces',serif;font-size:22px;color:{{ $c->overall_score>=75?'var(--g)':($c->overall_score>=55?'var(--or)':'var(--r)') }};">{{ $c->overall_score }}</span>/100</div>
        @if(count($c->alerts??[])>0)
          <div style="margin-top:5px;">
            @foreach($c->alerts as $a)
              <span class="badge br" style="margin-right:4px;font-size:9px;">{{ Str::limit($a,45) }}</span>
            @endforeach
          </div>
        @else
          <span class="badge bg" style="margin-top:5px;">No alerts</span>
        @endif
        @if($c->doctor_notes)
          <div style="font-size:12px;color:var(--gr);margin-top:5px;font-style:italic;">{{ Str::limit($c->doctor_notes,100) }}</div>
        @endif
        <a href="{{ route('parent.report', $student) }}" style="font-size:12px;color:var(--g);margin-top:6px;display:inline-block;">View full report →</a>
      </div>
    </div>
  @empty
    <div style="text-align:center;padding:24px;color:var(--gr);">No checkups completed yet.</div>
  @endforelse
</div>
@endsection
