@props(['product', 'flash' => false, 'sold' => null])

@php
    $disc = $product->discount_percent;
    $hasStar = $product->rating >= 4.9;
    $url = route('products.show', $product);
@endphp

<div class="pc" onclick="location.href='{{ $url }}'">
    @if($flash && $disc)
        <div class="pc-b" style="background:var(--red)">-{{ $disc }}%</div>
    @elseif($product->badge)
        <div class="pc-b{{ $product->badge === 'HOT' ? ' hot' : '' }}">{{ $product->badge }}</div>
    @endif

    <div class="pc-img"><img src="{{ $product->image ?: asset('images/placeholder-product.svg') }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}'"></div>
    <div class="pc-body">
        <div class="pc-name">{{ $product->name }}</div>
        <div class="pc-rat">
            <span class="stars">★★★★{{ $hasStar ? '★' : '☆' }}</span>
            <span class="rtxt">{{ number_format($product->rating, 1) }} ({{ $product->rating_count }})</span>
        </div>
        <div class="pc-price">Rp{{ number_format($product->price, 0, ',', '.') }}@if($product->old_price)<span class="pc-old">Rp{{ number_format($product->old_price, 0, ',', '.') }}</span>@endif</div>

        @if($flash && !is_null($sold))
            <div class="fs-bar-wrap"><div class="fs-bar" style="width:{{ $sold }}%"></div></div>
            <div class="fs-bar-lbl">Terjual {{ $sold }}%</div>
        @endif

        <form method="POST" action="{{ route('cart.add') }}" onclick="event.stopPropagation()">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <button class="btn-kj" type="submit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                + Keranjang
            </button>
        </form>
    </div>
</div>
