@extends('layouts.app')
@section('title', 'Kontak — NIVICO Electronic Mart')

@section('content')
<div class="kontak-wrap">
  <h2 style="font-family:'DM Serif Display',serif;font-size:26px;margin-bottom:20px">Hubungi Kami</h2>
  <div class="kontak-grid">
    <div class="kontak-info">
      <div class="kontak-card">
        <h2>Informasi Kontak</h2>
        <p>Kami siap membantu Anda! Hubungi kami melalui berbagai saluran berikut.</p>
        <div class="k-info-item"><div class="k-ico">📍</div><div class="k-inf"><strong>Alamat</strong><span>Jl. Raya Darmo No. 123, Wonokromo,<br>Surabaya, Jawa Timur 60241</span></div></div>
        <div class="k-info-item"><div class="k-ico">📞</div><div class="k-inf"><strong>Telepon</strong><span>(031) 123-4567<br>Senin–Sabtu, 08.00–17.00 WIB</span></div></div>
        <div class="k-info-item"><div class="k-ico">📧</div><div class="k-inf"><strong>Email</strong><span>info@nivico.id<br>support@nivico.id</span></div></div>
        <div class="k-info-item"><div class="k-ico">💬</div><div class="k-inf"><strong>WhatsApp</strong><span>+62 812-3456-7890<br>Balas dalam 1×24 jam</span></div></div>
        <div class="kontak-map">🗺 Peta Lokasi NIVICO Electronic Mart — Surabaya</div>
      </div>
      <a class="wa-card" href="https://wa.me/6281234567890" target="_blank" rel="noopener">
        <div class="wa-ico">💬</div>
        <div style="flex:1"><strong>Chat WhatsApp Sekarang</strong><span>Respon cepat, siap membantu Anda!</span></div>
        <svg width="20" height="20" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </div>
    <div class="kontak-form">
      <h2>Kirim Pesan</h2>
      <p>Ada pertanyaan atau masukan? Kirim pesan kepada kami dan kami akan merespons sesegera mungkin.</p>
      <form method="POST" action="{{ route('contact.store') }}">
        @csrf
        <div class="k-fg"><label>Nama Lengkap</label><input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap Anda" required></div>
        <div class="k-fg"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required></div>
        <div class="k-fg"><label>No. Telepon</label><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx"></div>
        <div class="k-fg"><label>Topik</label>
          <select name="topic">
            <option value="">Pilih topik</option>
            @foreach(['Pertanyaan Produk','Status Pesanan','Pengembalian Barang','Kerjasama / Partnership','Lainnya'] as $t)
              <option @selected(old('topic')===$t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="k-fg"><label>Pesan</label><textarea name="message" placeholder="Tuliskan pesan Anda di sini..." required>{{ old('message') }}</textarea></div>
        <button class="btn-send" type="submit">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Pesan
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
