@extends('layouts.app')
@section('title', 'Tentang Kami — NIVICO Electronic Mart')

@section('content')
@php
  $stats = $site['about.stats'] ?? [
    ['value' => '10K+', 'label' => 'Produk Tersedia'],
    ['value' => '50K+', 'label' => 'Pelanggan Puas'],
    ['value' => '9+', 'label' => 'Tahun Pengalaman'],
    ['value' => '34', 'label' => 'Provinsi Terjangkau'],
  ];
  $missions = $site['about.missions'] ?? [
    ['text' => 'Menyediakan produk elektronik berkualitas dengan harga terjangkau'],
    ['text' => 'Memberikan pelayanan pelanggan terbaik yang responsif dan profesional'],
    ['text' => 'Membangun ekosistem perdagangan elektronik yang transparan dan terpercaya'],
    ['text' => 'Terus berinovasi untuk memberikan pengalaman belanja terbaik'],
  ];
@endphp
<div class="about-wrap">
  <div class="about-hero">
    <h1>{{ $site['about.hero_title'] ?? 'Tentang NIVICO Electronic Mart' }}</h1>
    <p>{{ $site['about.hero_subtitle'] ?? 'Kami adalah toko elektronik terpercaya yang telah melayani ribuan pelanggan di seluruh Indonesia sejak 2015 dengan produk berkualitas dan harga terjangkau.' }}</p>
  </div>
  <div class="about-stats">
    @foreach($stats as $stat)
      <div class="stat-card"><div class="stat-num">{{ $stat['value'] ?? '' }}</div><div class="stat-lbl">{{ $stat['label'] ?? '' }}</div></div>
    @endforeach
  </div>
  <div class="about-grid">
    <div class="about-card">
      <h2>{{ $site['about.story_title'] ?? 'Cerita Kami' }}</h2>
      <p>{!! nl2br(e($site['about.story_body'] ?? "NIVICO Electronic Mart didirikan pada tahun 2015 di Surabaya, Jawa Timur. Berawal dari toko kecil yang menjual kabel dan aksesoris elektronik, kini kami telah berkembang menjadi salah satu toko elektronik online terpercaya di Indonesia.\n\nNama NIVICO terinspirasi dari semangat kami untuk memberikan nilai (value) terbaik kepada pelanggan melalui produk berkualitas dengan harga kompetitif. Kami berkomitmen untuk selalu menghadirkan produk original dan bersertifikat SNI.")) !!}</p>
    </div>
    <div class="about-card">
      <h2>{{ $site['about.vision_mission_title'] ?? 'Visi & Misi' }}</h2>
      <p><strong>{{ $site['about.vision_label'] ?? 'Visi:' }}</strong><br>{!! nl2br(e($site['about.vision_body'] ?? 'Menjadi marketplace elektronik nomor satu di Indonesia yang dipercaya oleh jutaan pelanggan dan mitra bisnis.')) !!}<br><br><strong>{{ $site['about.mission_label'] ?? 'Misi:' }}</strong></p>
      <ul>@foreach($missions as $mission)<li>{{ $mission['text'] ?? '' }}</li>@endforeach</ul>
    </div>
  </div>
</div>
@endsection
