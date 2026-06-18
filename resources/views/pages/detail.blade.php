@extends('layouts.app')
@section('title', $product->name . ' — NIVICO Electronic Mart')

@section('content')
<div class="det-wrap">
  <div class="bc">
    <a href="{{ route('home') }}">Beranda</a> ›
    <a href="{{ route('products.index') }}">Produk</a> ›
    <span>{{ $product->name }}</span>
  </div>

  <div class="det-grid">
    <div>
      <div class="det-main"><img id="det-img" src="{{ $product->image ?: asset('images/placeholder-product.svg') }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'"></div>
      <div class="det-thumbs" id="det-thumbs">
        <div class="det-th on" onclick="swImg('{{ $product->image ?: asset('images/placeholder-product.svg') }}', this)"><img src="{{ $product->image ?: asset('images/placeholder-product.svg') }}" loading="lazy" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'"></div>
        @foreach($product->images as $img)
          <div class="det-th" onclick="swImg('{{ $img->path }}', this)"><img src="{{ $img->path }}" loading="lazy" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'"></div>
        @endforeach
      </div>
    </div>

    <div class="det-info">
      <div><span class="det-cat-tag">{{ $product->category->name }}</span></div>
      <div class="det-title">{{ $product->name }}</div>
      <div class="det-rat">
        <span class="stars" style="font-size:14px">★★★★{{ $product->rating >= 4.9 ? '★' : '☆' }}</span>
        <span style="font-size:13px;color:var(--muted)">{{ number_format($product->rating, 1) }} • {{ $product->rating_count }} ulasan</span>
      </div>
      @php
        // Data varian untuk JS (hanya varian aktif). Produk single -> array kosong.
        $variantData = $product->has_variants
            ? $product->variants->where('is_active', true)->values()->map(fn ($v) => [
                'id'    => $v->id,
                'name'  => $v->name,
                'price' => (int) $v->price,
                'old'   => $v->old_price ? (int) $v->old_price : null,
                'disc'  => $v->discount_percent,
                'stock' => (int) $v->stock,
                'sku'   => $v->sku ?: $product->sku,
                'image' => $v->image,
              ])->all()
            : [];
        // Nilai awal tampilan: produk single pakai kolom produk; bervarian pakai varian termurah.
        $initStock = $product->has_variants ? 0 : (int) $product->stock;
        $initPrice = $product->has_variants ? (int) $product->min_price : (int) $product->price;
      @endphp

      <div class="det-pb">
        <div style="display:flex;align-items:baseline;gap:4px;flex-wrap:wrap">
          <span class="det-price" id="det-price">Rp{{ number_format($initPrice, 0, ',', '.') }}</span>
          @if($product->has_variants && $product->hasPriceRange())
            <span class="det-price-range" id="det-price-range" style="font-size:13px;color:var(--muted)">– Rp{{ number_format($product->max_price, 0, ',', '.') }}</span>
          @endif
          <span class="det-old" id="det-old" style="{{ $product->old_price ? '' : 'display:none' }}">@if($product->old_price)Rp{{ number_format($product->old_price, 0, ',', '.') }}@endif</span>
          <span class="det-disc" id="det-disc" style="{{ $product->old_price ? '' : 'display:none' }}">@if($product->old_price)-{{ $product->discount_percent }}%@endif</span>
        </div>
        <div class="det-stock" id="det-stock">
          @if($product->has_variants)
            <span style="color:var(--muted)">Pilih varian untuk melihat stok</span>
          @elseif($product->stock > 0) ✓ Stok Tersedia ({{ $product->stock }})
          @else <span style="color:var(--red)">✗ Stok Habis</span> @endif
        </div>
      </div>

      @if($product->has_variants)
        <div class="det-variants">
          <label class="var-label">Pilih Varian:</label>
          <div class="var-opts" id="var-opts">
            @foreach($product->variants->where('is_active', true)->values() as $v)
              <button type="button"
                class="var-opt{{ $v->stock < 1 ? ' soldout' : '' }}"
                data-vid="{{ $v->id }}"
                {{ $v->stock < 1 ? 'disabled' : '' }}>
                <span class="var-opt-name">{{ $v->name }}</span>
                <span class="var-opt-price">Rp{{ number_format($v->price, 0, ',', '.') }}</span>
                <span class="var-opt-stock">{{ $v->stock < 1 ? 'Habis' : 'Stok '.$v->stock }}</span>
              </button>
            @endforeach
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('cart.add') }}" id="det-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="variant_id" id="sel-variant-id" value="">
        <div class="det-qty" style="margin-bottom:14px">
          <label>Jumlah:</label>
          <div class="qty-c">
            <button class="qty-btn" type="button" onclick="chQty(-1)">−</button>
            <input class="qty-v" id="qty-v" name="qty" value="1" type="number" min="1" max="{{ max(1, $initStock) }}">
            <button class="qty-btn" type="button" onclick="chQty(1)">+</button>
          </div>
        </div>
        <div class="det-btns">
          <button class="btn-beli" type="submit" id="btn-beli" formaction="{{ route('cart.add') }}" name="redirect" value="checkout" {{ (!$product->has_variants && $product->stock < 1) ? 'disabled' : '' }}>Beli Sekarang</button>
          <button class="btn-cart-d" type="submit" id="btn-cart" {{ (!$product->has_variants && $product->stock < 1) ? 'disabled' : '' }}>+ Keranjang</button>
        </div>
        @if($product->has_variants)
          <div id="var-warn" style="display:none;color:var(--red);font-size:12.5px;margin-top:8px">Silakan pilih varian terlebih dahulu.</div>
        @endif
      </form>

      <script>
        window.__variantData = @json($variantData);
        window.__hasVariants = {{ $product->has_variants ? 'true' : 'false' }};
      </script>

      <div class="det-meta">
        <span><strong>SKU:</strong> {{ $product->sku }}</span>
        <span><strong>Kategori:</strong> {{ $product->category->name }}</span>
        <span><strong>Pengiriman:</strong> Estimasi 2–3 hari kerja</span>
        <span><strong>Garansi:</strong> 1 tahun resmi</span>
      </div>

      <div class="det-desc-box">
        <h3>Deskripsi Produk</h3>
        <p>{{ $product->description }}</p>
      </div>
    </div>
  </div>

  {{-- Produk Terkait --}}
  <div class="sw" style="margin-top:16px;border-radius:10px;overflow:hidden"><div class="sec" style="padding:0">
    <div class="sh" style="padding:16px 20px 12px"><h2>Produk Terkait</h2></div>
    <div class="pg" style="padding:0 20px 20px">
      @foreach($related as $p)
        <x-product-card :product="$p" />
      @endforeach
    </div>
  </div></div>
</div>
@endsection
