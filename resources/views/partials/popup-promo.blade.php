@php
    $popup = \App\Models\Promo::active()->where('badge', 'Member')->first()
        ?? \App\Models\Promo::active()->latest()->first();
@endphp
@if($popup)
<div id="popup-overlay">
  <div class="popup-box">
    <div class="popup-top">
      <button class="popup-close" onclick="closePopup()">✕</button>
      <span class="popup-emoji">🎉</span>
      <div class="popup-tag">Selamat Datang!</div>
      <h2>Diskon Spesial<br>Untuk Kamu</h2>
      <p>Gunakan kode di bawah ini dan hemat<br>langsung di pembelian pertamamu!</p>
    </div>
    <div class="popup-body">
      <div class="popup-code-wrap">
        <p>Kode Promo Eksklusif</p>
        <div class="popup-code">{{ $popup->code }}</div>
      </div>
      <div class="popup-perks">
        <div class="popup-perk"><span class="pp-ico">💰</span><strong>{{ $popup->title }}</strong><span>Semua produk</span></div>
        <div class="popup-perk"><span class="pp-ico">🚚</span><strong>Gratis Ongkir</strong><span>Min. Rp{{ number_format(config('shop.free_shipping_min'),0,',','.') }}</span></div>
        <div class="popup-perk"><span class="pp-ico">⏰</span><strong>Terbatas</strong><span>Jangan dilewatkan!</span></div>
      </div>
      <a class="btn-claim" href="{{ route('promo') }}">🛒 Lihat Semua Promo</a>
      <button class="popup-skip" onclick="closePopup()">Tidak, terima kasih</button>
      <div class="popup-exp">*{{ $popup->description ?? 'Berlaku untuk pembelian sesuai syarat & ketentuan.' }}</div>
    </div>
  </div>
</div>
@endif
