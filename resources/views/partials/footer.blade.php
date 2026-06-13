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
      <div class="logo-ring" style="width:46px;height:46px"><span class="r1" style="font-size:12px">NIVICO</span><span class="r2">Electronic Mart</span></div>
      <div><div style="color:#fff;font-size:19px;font-weight:800;font-family:'DM Serif Display',serif">NIVICO</div><div style="color:#94a3b8;font-size:11px">Electronic Mart</div></div>
    </div>
    <p>Pusat kebutuhan elektronik, aksesoris, tools, kabel, microphone, adaptor dan perlengkapan rumah tangga dengan harga terbaik.</p>
    <div class="ft-soc"><a href="#" aria-label="Instagram"><x-social-icon name="instagram" size="18" /></a><a href="#" aria-label="Tokopedia"><x-social-icon name="tokopedia" size="18" /></a><a href="#" aria-label="Shopee"><x-social-icon name="shopee" size="18" /></a><a href="#" aria-label="WhatsApp"><x-social-icon name="whatsapp" size="18" /></a></div>
  </div>
  <div class="ft-col"><h4>Layanan</h4><ul><li><a href="#">Cara Belanja</a></li><li><a href="#">Metode Pembayaran</a></li><li><a href="#">Info Pengiriman</a></li><li><a href="#">Pengembalian Barang</a></li><li><a href="#">FAQ</a></li></ul></div>
  <div class="ft-col"><h4>Kategori</h4><ul>
    @foreach(($navCategories ?? collect())->take(6) as $cat)
      <li><a href="{{ route('products.index', ['kategori' => $cat->slug]) }}">{{ $cat->name }}</a></li>
    @endforeach
  </ul></div>
  <div class="ft-col"><h4>Kontak</h4><ul><li><a href="#">📍 Surabaya, Jawa Timur</a></li><li><a href="#">📞 (031) 123-4567</a></li><li><a href="#">📧 info@nivico.id</a></li><li><a href="#">🕐 Senin–Sabtu 08.00–17.00</a></li></ul></div>
</div>
<div class="ft-bot">
  <p>© {{ date('Y') }} NIVICO Electronic Mart. All rights reserved.</p>
  <div class="pays"><span>Pembayaran:</span><span class="pay">BCA</span><span class="pay">MANDIRI</span><span class="pay">GOPAY</span><span class="pay">OVO</span><span class="pay">DANA</span><span class="pay">COD</span></div>
</div>
</footer>
