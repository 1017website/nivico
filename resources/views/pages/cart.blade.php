@extends('layouts.app')
@section('title', 'Keranjang Belanja — NIVICO')

@section('content')
<div class="cart-wrap">
  <h2 style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:18px">Keranjang Belanja</h2>

  @php $items = $cart->items; @endphp

  @if($items->isEmpty())
    <div class="c-items">
      <div class="empty-cart">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:block;margin:0 auto 14px"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <h3>Keranjang masih kosong</h3>
        <p>Yuk mulai belanja produk elektronik!</p>
        <a class="btn-co" style="width:auto;padding:12px 28px;margin-top:14px;display:inline-block;text-align:center" href="{{ route('products.index') }}">Mulai Belanja</a>
      </div>
    </div>
  @else
  <div class="cart-grid">
    <div>
      <div class="c-items">
        <div class="c-hd">
          <h2>Produk ({{ $qty }} item)</h2>
          <form method="POST" action="{{ route('cart.clear') }}">@csrf
            <button type="submit" style="background:none;border:none;color:var(--red);font-size:12.5px;font-weight:600;cursor:pointer">Hapus Semua</button>
          </form>
        </div>

        @foreach($items as $it)
          <div class="c-item">
            <div class="c-img"><img src="{{ $it->product->image ?: asset('images/placeholder-product.svg') }}" alt="{{ $it->product->name }}" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'"></div>
            <div class="c-inf">
              <div class="c-name">{{ $it->product->name }}</div>
              <div class="c-cat">{{ $it->product->category->name }}</div>
              <div class="c-price">Rp{{ number_format($it->product->price * $it->qty, 0, ',', '.') }}</div>
            </div>
            <div class="c-rt">
              <form method="POST" action="{{ route('cart.remove', $it->id) }}">@csrf @method('DELETE')
                <button class="c-del" type="submit" title="Hapus">🗑</button>
              </form>
              <div class="qty-c">
                <form method="POST" action="{{ route('cart.update', $it->id) }}" style="display:contents">@csrf @method('PATCH')
                  <input type="hidden" name="qty" value="{{ $it->qty - 1 }}">
                  <button class="qty-btn" type="submit">−</button>
                </form>
                <input class="qty-v" value="{{ $it->qty }}" style="width:36px" readonly>
                <form method="POST" action="{{ route('cart.update', $it->id) }}" style="display:contents">@csrf @method('PATCH')
                  <input type="hidden" name="qty" value="{{ $it->qty + 1 }}">
                  <button class="qty-btn" type="submit">+</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="sum-box">
      <div class="sum-ttl">Ringkasan Belanja</div>
      <div class="sum-row"><span>Total Produk</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
      <div class="sum-row"><span>Ongkos Kirim</span><span>{{ $freeShip ? 'GRATIS' : 'Rp'.number_format($shipping, 0, ',', '.') }}</span></div>
      <div class="sum-row"><span>Diskon</span><span style="color:var(--green)">−Rp{{ number_format($discount, 0, ',', '.') }}</span></div>

      <form method="POST" action="{{ route('cart.promo') }}" class="promo-inp">@csrf
        <input type="text" name="code" placeholder="Kode promo" value="{{ optional($cart->promo)->code }}">
        <button type="submit">Pakai</button>
      </form>
      @if($cart->promo)
        <div style="font-size:11.5px;color:var(--green);margin:-6px 0 8px">
          ✓ {{ $cart->promo->code }} aktif
          <form method="POST" action="{{ route('cart.promo.remove') }}" style="display:inline">@csrf @method('DELETE')
            <button type="submit" style="background:none;border:none;color:var(--red);font-size:11px;cursor:pointer">(hapus)</button>
          </form>
        </div>
      @endif

      <div class="sum-row tot"><span>Total Bayar</span><span>Rp{{ number_format($total, 0, ',', '.') }}</span></div>
      <a class="btn-co" href="{{ route('checkout') }}" style="display:block;text-align:center">Lanjut ke Checkout →</a>
    </div>
  </div>
  @endif
</div>
@endsection
