<!-- MOBILE NAV -->
<div class="mob-nav">
<div class="mn-inner">
  <a class="mn-item {{ request()->routeIs('home') ? 'on' : '' }}" href="{{ route('home') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Beranda</span></a>
  <a class="mn-item {{ request()->routeIs('promo') ? 'on' : '' }}" href="{{ route('promo') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg><span>Promo</span></a>
  <a class="mn-item {{ request()->routeIs('cart.*') ? 'on' : '' }}" href="{{ route('cart.index') }}" style="position:relative"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><span class="mn-badge" id="mb-badge">{{ $cartCount ?? 0 }}</span><span>Keranjang</span></a>
  <a class="mn-item {{ request()->routeIs('login') ? 'on' : '' }}" href="{{ auth()->check() ? route('home') : route('login') }}"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Akun</span></a>
</div>
</div>
