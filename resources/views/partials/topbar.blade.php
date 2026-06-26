<!-- TOPBAR -->
<div class="topbar">
<div class="tb">
  <div style="display:flex;align-items:center;gap:6px;opacity:.85;font-size:12px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
    Selamat datang di NIVICO Electronic Mart
  </div>
  <div class="tb-ctr">🚚 Gratis Ongkir untuk pembelian min. Rp{{ number_format(config('shop.free_shipping_min'), 0, ',', '.') }}</div>
  <div class="tb-right">
    @php
      // Normalisasi nomor WA -> link wa.me. Bila admin isi URL penuh, pakai apa adanya.
      $waRaw = trim($site['social.whatsapp'] ?? '');
      if ($waRaw !== '' && ! \Illuminate\Support\Str::startsWith($waRaw, ['http://', 'https://'])) {
          $waDigits = preg_replace('/\D+/', '', $waRaw);
          if (\Illuminate\Support\Str::startsWith($waDigits, '0')) { $waDigits = '62'.substr($waDigits, 1); }
          $waRaw = $waDigits !== '' ? 'https://wa.me/'.$waDigits : '';
      }
      $socials = [
        ['key' => 'instagram', 'url' => trim($site['social.instagram'] ?? ''), 'label' => 'Instagram', 'icon' => trim($site['social.instagram_icon'] ?? '')],
        ['key' => 'tokopedia', 'url' => trim($site['social.tokopedia'] ?? ''), 'label' => 'Tokopedia', 'icon' => trim($site['social.tokopedia_icon'] ?? '')],
        ['key' => 'shopee',    'url' => trim($site['social.shopee'] ?? ''),    'label' => 'Shopee',    'icon' => trim($site['social.shopee_icon'] ?? '')],
        ['key' => 'whatsapp',  'url' => $waRaw,                                 'label' => 'WhatsApp',  'icon' => trim($site['social.whatsapp_icon'] ?? '')],
      ];
    @endphp
    @foreach($socials as $s)
      @if($s['url'] !== '')
        <a href="{{ $s['url'] }}" target="_blank" rel="noopener"><x-social-icon :name="$s['key']" :img="$s['icon']" size="13" /> {{ $s['label'] }}</a>
      @endif
    @endforeach
  </div>
</div>
</div>
