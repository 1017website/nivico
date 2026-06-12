@extends('layouts.app')
@section('title', 'NIVICO Electronic Mart — Belanja Elektronik Terpercaya')

@section('content')

{{-- HERO --}}
<div class="hero-wrap">
<div class="hero">
  <div class="sl on">
    <div class="sl-l">
      <div><div class="sl-t1">NIVICO</div><div class="sl-t2">Electronic Mart</div><div class="sl-desc">Pusat kebutuhan elektronik, aksesoris, tools, kabel, microphone, adaptor dan perlengkapan rumah tangga dengan harga terbaik.</div></div>
      <div class="sl-perks">
        <div class="sp"><div class="sp-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div><div class="sp-t"><div class="t1">Produk</div><div class="t2">Berkualitas</div></div></div>
        <div class="sp"><div class="sp-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div><div class="sp-t"><div class="t1">Pengiriman</div><div class="t2">Cepat</div></div></div>
        <div class="sp"><div class="sp-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div><div class="sp-t"><div class="t1">Pembayaran</div><div class="t2">Aman</div></div></div>
        <div class="sp"><div class="sp-ico"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.39 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.78a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg></div><div class="sp-t"><div class="t1">Layanan</div><div class="t2">Terbaik</div></div></div>
      </div>
    </div>
    <div class="sl-r"><img src="https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=500&q=80" alt="produk"></div>
  </div>
  <div class="sl">
    <div class="sl-l">
      <div><div class="sl-t1" style="font-size:36px">Flash Sale</div><div class="sl-t2" style="font-size:36px">Diskon 50%</div><div class="sl-desc">Penawaran terbatas untuk produk elektronik pilihan!</div><a class="hero-cta" href="{{ route('promo') }}">Lihat Promo →</a></div>
      <div class="sl-perks" style="visibility:hidden"><div class="sp"><div class="sp-ico"></div><div class="sp-t"><div class="t1">x</div></div></div></div>
    </div>
    <div class="sl-r"><img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500&q=80" alt="promo"></div>
  </div>
  <div class="sl">
    <div class="sl-l">
      <div><div class="sl-t1" style="font-size:36px">Koleksi</div><div class="sl-t2" style="font-size:36px">Terbaru 2024</div><div class="sl-desc">Produk elektronik terbaru dengan kualitas premium dan harga terjangkau.</div><a class="hero-cta" href="{{ route('products.index') }}">Lihat Produk →</a></div>
      <div class="sl-perks" style="visibility:hidden"><div class="sp"><div class="sp-ico"></div><div class="sp-t"><div class="t1">x</div></div></div></div>
    </div>
    <div class="sl-r"><img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80" alt="koleksi"></div>
  </div>
  <div class="sl-dots"><div class="sd on" onclick="goSl(0)"></div><div class="sd" onclick="goSl(1)"></div><div class="sd" onclick="goSl(2)"></div></div>
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
  <div class="sh"><h2>PRODUK TERBARU</h2><a href="{{ route('products.index') }}">Lihat Semua ›</a></div>
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
    <div class="fs-title">⚡ Flash Sale</div>
    <div class="cd" style="margin-left:auto">
      <span class="cd-lbl">Berakhir dalam:</span>
      <div class="cd-box" id="cdH">04</div><span class="cd-sep">:</span>
      <div class="cd-box" id="cdM">32</div><span class="cd-sep">:</span>
      <div class="cd-box" id="cdS">10</div>
    </div>
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
<div class="sw"><div class="sec">
  <div class="sh"><h2>PENAWARAN SPESIAL</h2></div>
  <div class="promo-grid">
    <a class="promo-card" href="{{ route('promo') }}"><img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=700&q=80" alt="promo1"><div class="promo-ov"><span>🔥 Promo Spesial</span><h3>Belanja Min. Rp200rb<br>Diskon 20%</h3><div class="btn-promo">Klaim Sekarang →</div></div></a>
    <a class="promo-card" href="{{ route('promo') }}"><img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?w=700&q=80" alt="promo2"><div class="promo-ov"><span>⚡ Flash Deal</span><h3>Gratis Ongkir<br>Seluruh Indonesia</h3><div class="btn-promo">Belanja Sekarang →</div></div></a>
  </div>
</div></div>

{{-- BEST SELLER --}}
<div class="sw"><div class="sec">
  <div class="sh"><h2>BEST SELLER</h2><a href="{{ route('products.index', ['sort' => 'terlaris']) }}">Lihat Semua ›</a></div>
  <div class="pg">
    @foreach($bestProducts as $p)
      <x-product-card :product="$p" />
    @endforeach
  </div>
</div></div>

@include('partials.popup-promo')
@endsection
