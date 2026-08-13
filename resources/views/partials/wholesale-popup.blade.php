@php
  $wholesaleEnabled = $site['wholesale.enabled'] ?? true;
  $wholesaleWaRaw = trim($site['social.whatsapp'] ?? '') ?: trim($site['contact.phone'] ?? '');
  $wholesaleWaNumber = preg_replace('/\D+/', '', $wholesaleWaRaw);

  if (str_starts_with($wholesaleWaNumber, '0')) {
      $wholesaleWaNumber = '62'.substr($wholesaleWaNumber, 1);
  } elseif ($wholesaleWaNumber !== '' && ! str_starts_with($wholesaleWaNumber, '62')) {
      $wholesaleWaNumber = '62'.$wholesaleWaNumber;
  }

  $wholesaleMessage = trim($site['wholesale.message'] ?? '')
      ?: 'Halo NIVICO, saya ingin bertanya mengenai harga dan pemesanan grosir.';
  $wholesaleWaUrl = $wholesaleWaNumber !== ''
      ? 'https://wa.me/'.$wholesaleWaNumber.'?text='.rawurlencode($wholesaleMessage)
      : null;
@endphp

@if($wholesaleEnabled && $wholesaleWaUrl)
  <aside id="wholesale-popup" class="wholesale-popup" role="dialog" aria-modal="false" aria-labelledby="wholesale-popup-title" aria-hidden="true">
    <button type="button" class="wholesale-popup-close" aria-label="Tutup penawaran grosir" data-wholesale-close>&times;</button>
    <div class="wholesale-popup-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></div>
    <div class="wholesale-popup-copy">
      <span class="wholesale-popup-tag">MELAYANI PEMBELIAN GROSIR</span>
      <h2 id="wholesale-popup-title">{{ $site['wholesale.title'] ?? 'Butuh Harga Grosir?' }}</h2>
      <p>{{ $site['wholesale.subtitle'] ?? 'Dapatkan penawaran khusus untuk pembelian dalam jumlah besar.' }}</p>
      <a href="{{ $wholesaleWaUrl }}" target="_blank" rel="noopener" class="wholesale-popup-button">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        {{ $site['wholesale.button_text'] ?? 'Hubungi via WhatsApp' }}
      </a>
    </div>
  </aside>
@endif
