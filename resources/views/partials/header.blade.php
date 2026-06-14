<!-- HEADER -->
<header class="header">
<div class="hd">
  <a class="logo" href="{{ route('home') }}">
    @if(!empty($site['brand.logo']))
      <img src="{{ $site['brand.logo'] }}" alt="{{ $site['brand.name'] ?? 'NIVICO' }}" style="height:46px;width:auto">
    @else
      <div class="logo-ring"><span class="r1">{{ $site['brand.name'] ?? 'NIVICO' }}</span><span class="r2">{{ $site['brand.tagline'] ?? 'Electronic Mart' }}</span></div>
      <div class="logo-name"><div class="n1">{{ $site['brand.name'] ?? 'NIVICO' }}</div><div class="n2">{{ $site['brand.tagline'] ?? 'Electronic Mart' }}</div></div>
    @endif
  </a>
  <form class="srch" action="{{ route('products.index') }}" method="GET">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk elektronik, kabel, adaptor, dll...">
    <div class="srch-sep"></div>
    <select class="srch select" name="kategori">
      <option value="">Semua Kategori</option>
      @foreach($navCategories ?? [] as $cat)
        <option value="{{ $cat->slug }}" @selected(request('kategori')===$cat->slug)>{{ $cat->name }}</option>
      @endforeach
    </select>
    <button class="srch-btn" type="submit"><svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></button>
  </form>
  <div class="h-acts">
    @auth
      <a class="h-act" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('home') }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>{{ \Illuminate\Support\Str::limit(auth()->user()->first_name, 10) }}</span>
      </a>
      <form method="POST" action="{{ route('logout') }}" style="display:contents">@csrf
        <button class="h-act" type="submit" style="background:none;border:none">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span>Keluar</span>
        </button>
      </form>
    @else
      <a class="h-act" href="{{ route('login') }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Masuk / Daftar</span>
      </a>
    @endauth
    <a class="h-act" href="{{ route('cart.index') }}">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <span class="badge" id="cnum">{{ $cartCount ?? 0 }}</span><span>Keranjang</span>
    </a>
  </div>
</div>
</header>
