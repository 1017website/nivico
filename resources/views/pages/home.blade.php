@extends('layouts.app')
@section('title', 'NIVICO Electronic Mart — Belanja Elektronik Terpercaya')

@section('content')

{{-- HERO --}}
@php
  $slides = $site['hero.slides'] ?? [];
  $perks  = $site['hero.perks'] ?? [];
  $perkIcons = [
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.78a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>',
  ];
@endphp
<div class="hero-wrap">
<div class="hero">
  @foreach($slides as $idx => $sl)
  <div class="sl {{ $idx === 0 ? 'on' : '' }}">
    <div class="sl-l">
      <div>
        <div class="sl-t1" @if($idx>0)style="font-size:36px"@endif>{{ $sl['title1'] ?? '' }}</div>
        <div class="sl-t2" @if($idx>0)style="font-size:36px"@endif>{{ $sl['title2'] ?? '' }}</div>
        <div class="sl-desc">{{ $sl['desc'] ?? '' }}</div>
        @if(!empty($sl['cta_text']))<a class="hero-cta" href="{{ $sl['cta_link'] ?: '#' }}">{{ $sl['cta_text'] }}</a>@endif
      </div>
      @if($idx === 0 && !empty($perks))
      <div class="sl-perks">
        @foreach($perks as $pi => $perk)
          <div class="sp"><div class="sp-ico">{!! $perkIcons[$pi % count($perkIcons)] !!}</div><div class="sp-t"><div class="t1">{{ $perk['t1'] ?? '' }}</div><div class="t2">{{ $perk['t2'] ?? '' }}</div></div></div>
        @endforeach
      </div>
      @else
      <div class="sl-perks" style="visibility:hidden"><div class="sp"><div class="sp-ico"></div><div class="sp-t"><div class="t1">x</div></div></div></div>
      @endif
    </div>
    <div class="sl-r"><img src="{{ $sl['image'] ?? '' }}" alt="slide {{ $idx+1 }}"></div>
  </div>
  @endforeach
  <div class="sl-dots">@foreach($slides as $idx => $sl)<div class="sd {{ $idx === 0 ? 'on' : '' }}" onclick="goSl({{ $idx }})"></div>@endforeach</div>
  <div class="sl-arr p" onclick="prevSl()">&#8249;</div>
  <div class="sl-arr n" onclick="nextSl()">&#8250;</div>
</div>
</div>

{{-- CATS --}}
<div class="cats-wrap">
<div class="cats">
  @foreach($categories as $cat)
    <a class="cat-i" href="{{ route('products.index', ['kategori' => $cat->slug]) }}" @if($loop->last) style="border-right:none" @endif>
      {!! $cat->icon ?? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' !!}
      <span>{{ $cat->name }}</span>
    </a>
  @endforeach
</div>
</div>

{{-- PRODUK TERBARU --}}
<div class="sw" style="margin-top:14px"><div class="sec">
  <div class="sh"><h2>{{ $site['section.new_title'] ?? 'PRODUK TERBARU' }}</h2><a href="{{ route('products.index') }}">Lihat Semua ›</a></div>
  <div class="pg">
    @foreach($newProducts as $p)
      <x-product-card :product="$p" />
    @endforeach
  </div>
</div></div>

{{-- FLASH SALE --}}
@if($flashProducts->isNotEmpty())
<div class="sw"><div class="sec">
  <div class="fs-head">
    <div class="fs-title">{{ $site['section.flash_title'] ?? '⚡ Flash Sale' }}</div>
    @if(($site['flashsale.enabled'] ?? true) && !empty($site['flashsale.ends_at']))
    <div class="cd" style="margin-left:auto" data-ends="{{ \Illuminate\Support\Carbon::parse($site['flashsale.ends_at'])->toIso8601String() }}">
      <span class="cd-lbl">{{ $site['flashsale.label'] ?? 'Berakhir dalam:' }}</span>
      <div class="cd-box" id="cdH">00</div><span class="cd-sep">:</span>
      <div class="cd-box" id="cdM">00</div><span class="cd-sep">:</span>
      <div class="cd-box" id="cdS">00</div>
    </div>
    @endif
    <a href="{{ route('products.index', ['sort' => 'terlaris']) }}" style="color:var(--blue);font-size:12.5px;font-weight:600;margin-left:14px">Lihat Semua ›</a>
  </div>
  <div class="pg">
    @php $pcts = [75, 60, 90, 45, 55, 30]; @endphp
    @foreach($flashProducts as $i => $p)
      <x-product-card :product="$p" :flash="true" :sold="$pcts[$i % count($pcts)]" />
    @endforeach
  </div>
</div></div>
@endif

{{-- PROMO BANNER --}}
@php $banners = $site['banner.promos'] ?? []; @endphp
@if(!empty($banners))
<div class="sw"><div class="sec">
  <div class="sh"><h2>{{ $site['section.promo_title'] ?? 'PENAWARAN SPESIAL' }}</h2></div>
  <div class="promo-grid">
    @foreach($banners as $b)
    <a class="promo-card" href="{{ $b['link'] ?: route('promo') }}"><img src="{{ $b['image'] ?? '' }}" alt="banner"><div class="promo-ov"><span>{{ $b['tag'] ?? '' }}</span><h3>{!! $b['title'] ?? '' !!}</h3><div class="btn-promo">{{ $b['btn'] ?? 'Lihat →' }}</div></div></a>
    @endforeach
  </div>
</div></div>
@endif

{{-- BEST SELLER --}}
<div class="sw"><div class="sec">
  <div class="sh"><h2>{{ $site['section.best_title'] ?? 'BEST SELLER' }}</h2><a href="{{ route('products.index', ['sort' => 'terlaris']) }}">Lihat Semua ›</a></div>
  <div class="pg">
    @foreach($bestProducts as $p)
      <x-product-card :product="$p" />
    @endforeach
  </div>
</div></div>

@include('partials.popup-promo')
@endsection
