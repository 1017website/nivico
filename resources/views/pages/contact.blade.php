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
        @php
          $kAddress = trim($site['contact.address'] ?? '') ?: 'Surabaya, Jawa Timur, Indonesia';
          $kPhone   = trim($site['contact.phone'] ?? '');
          $kEmail   = trim($site['contact.email'] ?? '');
          $kHours   = trim($site['contact.hours'] ?? '') ?: 'Senin–Sabtu, 08.00–17.00 WIB';

          // ── WhatsApp ──
          // social.whatsapp diisi NOMOR saja (mis. 0812xxxx / 62812xxxx).
          // Bila admin terlanjur isi URL penuh, kita ekstrak nomornya.
          $waRaw = trim($site['social.whatsapp'] ?? '');
          if ($waRaw === '') { $waRaw = $kPhone; }
          $waDigits = '';
          if ($waRaw !== '') {
              // ambil hanya angka (URL pun akan terambil nomornya, mis. phone=62812...)
              $waDigits = preg_replace('/\D+/', '', $waRaw);
              if (\Illuminate\Support\Str::startsWith($waDigits, '0')) {
                  $waDigits = '62'.substr($waDigits, 1);
              }
          }
          // Pesan default (bisa diatur dari CMS)
          $waMessage = trim($site['wa.default_message'] ?? '') ?: 'Halo, saya ingin bertanya tentang produk NIVICO.';
          $waLink = $waDigits !== '' ? 'https://wa.me/'.$waDigits.'?text='.rawurlencode($waMessage) : '';
          // Tampilan nomor: 0812... (lebih familiar) dari digit 62...
          $waDisplay = '';
          if ($waDigits !== '') {
              $waDisplay = \Illuminate\Support\Str::startsWith($waDigits, '62') ? '0'.substr($waDigits, 2) : $waDigits;
          }

          // ── Google Maps embed ──
          // maps.embed bisa berupa: URL src embed (https://www.google.com/maps/embed?...),
          // tag <iframe> lengkap, atau link share biasa. Kita normalisasi ke src iframe.
          $mapsRaw = trim($site['maps.embed'] ?? '');
          $mapsSrc = '';
          if ($mapsRaw !== '') {
              if (\Illuminate\Support\Str::contains($mapsRaw, '<iframe')) {
                  // ekstrak atribut src dari tag iframe yang ditempel admin
                  if (preg_match('/src=["\']([^"\']+)["\']/', $mapsRaw, $m)) {
                      $mapsSrc = $m[1];
                  }
              } elseif (\Illuminate\Support\Str::startsWith($mapsRaw, 'http')) {
                  $mapsSrc = $mapsRaw;
              }
          }
        @endphp
        <div class="k-info-item"><div class="k-ico">📍</div><div class="k-inf"><strong>Alamat</strong><span>{!! nl2br(e($kAddress)) !!}</span></div></div>
        @if($kPhone !== '')
        <div class="k-info-item"><div class="k-ico">📞</div><div class="k-inf"><strong>Telepon</strong><span>{{ $kPhone }}<br>{{ $kHours }}</span></div></div>
        @endif
        @if($kEmail !== '')
        <div class="k-info-item"><div class="k-ico">📧</div><div class="k-inf"><strong>Email</strong><span>{{ $kEmail }}</span></div></div>
        @endif
        @if($waDisplay !== '')
        <div class="k-info-item"><div class="k-ico">💬</div><div class="k-inf"><strong>WhatsApp</strong><span>{{ $waDisplay }}<br>Balas dalam 1×24 jam</span></div></div>
        @endif
        @if($mapsSrc !== '')
        <div class="kontak-map" style="padding:0;overflow:hidden">
          <iframe src="{{ $mapsSrc }}" width="100%" height="260" style="border:0;display:block" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi NIVICO Electronic Mart"></iframe>
        </div>
        @else
        <div class="kontak-map">🗺 Peta Lokasi NIVICO Electronic Mart — Surabaya</div>
        @endif
      </div>
      @if($waLink !== '')
      @php
        $waCardTitle = trim($site['wa.card_title'] ?? '') ?: 'Chat WhatsApp Sekarang';
        $waCardSub   = trim($site['wa.card_subtitle'] ?? '') ?: 'Respon cepat, siap membantu Anda!';
        $waCardIcon  = trim($site['social.whatsapp_icon'] ?? '');
      @endphp
      <a class="wa-card" href="{{ $waLink }}" target="_blank" rel="noopener">
        <div class="wa-ico">
          @if($waCardIcon !== '')
            <img src="{{ $waCardIcon }}" alt="WhatsApp" style="width:26px;height:26px;object-fit:contain">
          @else
            <i class="fa-brands fa-whatsapp" style="font-size:26px;line-height:1;color:#fff" aria-hidden="true"></i>
          @endif
        </div>
        <div style="flex:1"><strong>{{ $waCardTitle }}</strong><span>{{ $waCardSub }}</span></div>
        <svg width="20" height="20" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </a>
      @endif
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
