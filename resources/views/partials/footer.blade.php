<!-- FEATURES -->
<div class="feat-bar">
<div class="feat-inner">
  <div class="feat-item"><div class="feat-ico">🚚</div><div class="feat-txt"><strong>Pengiriman Cepat</strong><p>Gratis ongkir min. Rp{{ number_format(config('shop.free_shipping_min'),0,',','.') }}</p></div></div>
  <div class="feat-item"><div class="feat-ico">🔒</div><div class="feat-txt"><strong>Pembayaran Aman</strong><p>Transaksi 100% terlindungi</p></div></div>
  <div class="feat-item"><div class="feat-ico">✅</div><div class="feat-txt"><strong>Produk Original</strong><p>Bergaransi resmi & SNI</p></div></div>
  <div class="feat-item"><div class="feat-ico">🎧</div><div class="feat-txt"><strong>Layanan 24/7</strong><p>Siap membantu kapan saja</p></div></div>
</div>
</div>

<!-- FOOTER -->
<footer class="footer">
<div class="ft">
  <div class="ft-brand ft-col">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
      @if(!empty($site['brand.logo']))
        <img src="{{ $site['brand.logo'] }}" alt="{{ $site['brand.name'] ?? 'NIVICO' }}" style="height:46px;width:auto">
      @else
        <div class="logo-ring" style="width:46px;height:46px"><span class="r1" style="font-size:12px">{{ $site['brand.name'] ?? 'NIVICO' }}</span><span class="r2">{{ $site['brand.tagline'] ?? 'Electronic Mart' }}</span></div>
        <div><div style="color:#fff;font-size:19px;font-weight:800;font-family:'DM Serif Display',serif">{{ $site['brand.name'] ?? 'NIVICO' }}</div><div style="color:#94a3b8;font-size:11px">{{ $site['brand.tagline'] ?? 'Electronic Mart' }}</div></div>
      @endif
    </div>
    <p>{{ $site['footer.about'] ?? 'Pusat kebutuhan elektronik, aksesoris, tools, kabel, microphone, adaptor dan perlengkapan rumah tangga dengan harga terbaik.' }}</p>
    @php
      // WhatsApp diisi NOMOR -> ubah jadi link wa.me + pesan default.
      $waNum = preg_replace('/\D+/', '', trim($site['social.whatsapp'] ?? ''));
      if (\Illuminate\Support\Str::startsWith($waNum, '0')) { $waNum = '62'.substr($waNum, 1); }
      $waFooterUrl = '';
      if ($waNum !== '') {
          $waMsg = trim($site['wa.default_message'] ?? '') ?: 'Halo, saya ingin bertanya tentang produk NIVICO.';
          $waFooterUrl = 'https://wa.me/'.$waNum.'?text='.rawurlencode($waMsg);
      }
      $socials = [
        'instagram' => $site['social.instagram'] ?? '',
        'tokopedia' => $site['social.tokopedia'] ?? '',
        'shopee'    => $site['social.shopee'] ?? '',
        'whatsapp'  => $waFooterUrl,
        'facebook'  => $site['social.facebook'] ?? '',
        'tiktok'    => $site['social.tiktok'] ?? '',
      ];
      $socials = array_filter($socials);
    @endphp
    @if(!empty($socials))
    <div class="ft-soc">
      @foreach($socials as $name => $url)
        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($name) }}"><x-social-icon :name="$name" :img="trim($site['social.'.$name.'_icon'] ?? '')" size="18" /></a>
      @endforeach
    </div>
    @endif
  </div>
  <div class="ft-col"><h4>Layanan</h4><ul><li><a href="#">Cara Belanja</a></li><li><a href="#">Metode Pembayaran</a></li><li><a href="#">Info Pengiriman</a></li><li><a href="#">Pengembalian Barang</a></li><li><a href="#">FAQ</a></li></ul></div>
  <div class="ft-col"><h4>Kategori</h4><ul>
    @foreach(($navCategories ?? collect())->take(6) as $cat)
      <li><a href="{{ route('products.index', ['kategori' => $cat->slug]) }}">{{ $cat->name }}</a></li>
    @endforeach
  </ul></div>
  <div class="ft-col"><h4>Kontak</h4><ul>
    @if(!empty($site['contact.address']))<li><a href="#">📍 {{ $site['contact.address'] }}</a></li>@endif
    @if(!empty($site['contact.phone']))<li><a href="tel:{{ preg_replace('/[^0-9+]/','',$site['contact.phone']) }}">📞 {{ $site['contact.phone'] }}</a></li>@endif
    @if(!empty($site['contact.email']))<li><a href="mailto:{{ $site['contact.email'] }}">📧 {{ $site['contact.email'] }}</a></li>@endif
    @if(!empty($site['contact.hours']))<li><a href="#">🕐 {{ $site['contact.hours'] }}</a></li>@endif
  </ul></div>
</div>
<div class="ft-bot">
  <p>{{ $site['footer.copyright'] ?? '© '.date('Y').' NIVICO Electronic Mart. All rights reserved.' }}</p>
  <div class="pays"><span>Pembayaran:</span><span class="pay">BCA</span><span class="pay">MANDIRI</span><span class="pay">GOPAY</span><span class="pay">OVO</span><span class="pay">DANA</span><span class="pay">COD</span></div>
</div>
</footer>
