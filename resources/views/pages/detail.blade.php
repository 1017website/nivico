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
      <div class="det-main"><img id="det-img" src="{{ $product->image }}" alt="{{ $product->name }}"></div>
      <div class="det-thumbs" id="det-thumbs">
        <div class="det-th on" onclick="swImg('{{ $product->image }}', this)"><img src="{{ $product->image }}" loading="lazy"></div>
        @foreach($product->images as $img)
          <div class="det-th" onclick="swImg('{{ $img->path }}', this)"><img src="{{ $img->path }}" loading="lazy"></div>
        @endforeach
        @foreach($related->take(3) as $rel)
          <div class="det-th" onclick="swImg('{{ $rel->image }}', this)"><img src="{{ $rel->image }}" loading="lazy"></div>
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
      <div class="det-pb">
        <div style="display:flex;align-items:baseline;gap:4px;flex-wrap:wrap">
          <span class="det-price">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
          @if($product->old_price)
            <span class="det-old">Rp{{ number_format($product->old_price, 0, ',', '.') }}</span>
            <span class="det-disc">-{{ $product->discount_percent }}%</span>
          @endif
        </div>
        <div class="det-stock">
          @if($product->stock > 0) ✓ Stok Tersedia ({{ $product->stock }}) @else <span style="color:var(--red)">✗ Stok Habis</span> @endif
        </div>
      </div>

      <form method="POST" action="{{ route('cart.add') }}" id="det-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <div class="det-qty" style="margin-bottom:14px">
          <label>Jumlah:</label>
          <div class="qty-c">
            <button class="qty-btn" type="button" onclick="chQty(-1)">−</button>
            <input class="qty-v" id="qty-v" name="qty" value="1" type="number" min="1" max="{{ max(1,$product->stock) }}">
            <button class="qty-btn" type="button" onclick="chQty(1)">+</button>
          </div>
        </div>
        <div class="det-btns">
          <button class="btn-beli" type="submit" formaction="{{ route('cart.add') }}" name="redirect" value="checkout" {{ $product->stock < 1 ? 'disabled' : '' }}>Beli Sekarang</button>
          <button class="btn-cart-d" type="submit" {{ $product->stock < 1 ? 'disabled' : '' }}>+ Keranjang</button>
        </div>
      </form>

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
