<!-- NAV -->
<nav class="nav">
<div class="nv">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">BERANDA</a>
  <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">PRODUK</a>
  <a href="{{ route('products.index') }}">KATEGORI ▾</a>
  <a href="{{ route('promo') }}" class="{{ request()->routeIs('promo') ? 'active' : '' }}">PROMO</a>
  <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">TENTANG KAMI</a>
  <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">KONTAK</a>
</div>
</nav>
