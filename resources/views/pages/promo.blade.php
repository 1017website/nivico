@extends('layouts.app')
@section('title', 'Promo & Penawaran — NIVICO')

@section('content')
<div class="promo-wrap">
  <div class="promo-hero">
    <div class="ph-left">
      <h1>Promo &amp; Penawaran<br>Spesial Untukmu</h1>
      <p>Nikmati berbagai diskon menarik, voucher, dan gratis ongkir untuk belanja elektronik kebutuhanmu setiap hari!</p>
      <a class="hero-cta" href="{{ route('products.index') }}" style="background:#fff;color:var(--navy)">Belanja Sekarang →</a>
    </div>
    <div class="ph-right">🎁</div>
  </div>

  @php
    $tabs = ['semua'=>'Semua','flash'=>'Flash Sale','voucher'=>'Voucher','ongkir'=>'Gratis Ongkir','cashback'=>'Cashback'];
    $active = $tab ?: 'semua';
  @endphp
  <div class="promo-tabs">
    @foreach($tabs as $key => $label)
      <a class="ptab {{ $active===$key ? 'on' : '' }}" href="{{ $key==='semua' ? route('promo') : route('promo', ['tab'=>$key]) }}">{{ $label }}</a>
    @endforeach
  </div>

  @if($promos->isEmpty())
    <div class="empty-cart" style="background:#fff;border:1px solid var(--border);border-radius:10px"><h3>Belum ada promo</h3><p>Promo untuk kategori ini sedang kosong. Cek lagi nanti ya!</p></div>
  @else
  <div class="promo-cards">
    @foreach($promos as $promo)
      <div class="p-card" onclick="location.href='{{ route('products.index') }}'">
        <div class="p-card-img">
          <img src="{{ $promo->image ?: asset('images/placeholder-banner.svg') }}" alt="{{ $promo->title }}" onerror="this.onerror=null;this.src='/images/placeholder-banner.svg'">
          @if($promo->badge)<span class="p-card-badge">{{ $promo->badge }}</span>@endif
        </div>
        <div class="p-card-body">
          <h3>{{ $promo->title }}</h3>
          <p>{{ $promo->description }}</p>
          <div class="p-card-footer">
            <span class="p-card-exp">{{ $promo->expires_at ? 's/d '.$promo->expires_at->format('d M Y') : 'Tanpa batas waktu' }}</span>
            <span class="p-card-code">{{ $promo->code }}</span>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  @endif
</div>
@endsection
