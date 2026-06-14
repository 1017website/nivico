@php
    $popupOn = $site['popup.enabled'] ?? true;
    $code = $site['popup.promo_code'] ?? '';
    $autoTitle = ''; $autoDesc = '';
    if (! $code) {
        $auto = \App\Models\Promo::active()->where('badge', 'Member')->first()
            ?? \App\Models\Promo::active()->latest()->first();
        $code = $auto->code ?? '';
        $autoTitle = $auto->title ?? '';
        $autoDesc  = $auto->description ?? '';
    }
@endphp
@if($popupOn && $code)
<div id="popup-overlay">
  <div class="popup-box">
    <div class="popup-top">
      <button class="popup-close" onclick="closePopup()">✕</button>
      <span class="popup-emoji">🎉</span>
      <div class="popup-tag">{{ $site['popup.tag'] ?? 'Selamat Datang!' }}</div>
      <h2>{!! $site['popup.title'] ?? 'Diskon Spesial<br>Untuk Kamu' !!}</h2>
      <p>{{ $site['popup.subtitle'] ?? 'Gunakan kode di bawah ini dan hemat langsung di pembelian pertamamu!' }}</p>
    </div>
    <div class="popup-body">
      <div class="popup-code-wrap">
        <p>Kode Promo Eksklusif</p>
        <div class="popup-code">{{ $code }}</div>
      </div>
      <div class="popup-perks">
        <div class="popup-perk"><span class="pp-ico">💰</span><strong>{{ $autoTitle ?: 'Hemat' }}</strong><span>Semua produk</span></div>
        <div class="popup-perk"><span class="pp-ico">🚚</span><strong>Gratis Ongkir</strong><span>Min. Rp{{ number_format(config('shop.free_shipping_min'),0,',','.') }}</span></div>
        <div class="popup-perk"><span class="pp-ico">⏰</span><strong>Terbatas</strong><span>Jangan dilewatkan!</span></div>
      </div>
      <a class="btn-claim" href="{{ route('promo') }}">{{ $site['popup.btn_text'] ?? '🛒 Lihat Semua Promo' }}</a>
      <button class="popup-skip" onclick="closePopup()">Tidak, terima kasih</button>
      <div class="popup-exp">*{{ $autoDesc ?: 'Berlaku untuk pembelian sesuai syarat & ketentuan.' }}</div>
    </div>
  </div>
</div>
@endif
