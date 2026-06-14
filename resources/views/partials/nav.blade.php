<!-- NAV -->
<nav class="nav">
<div class="nv">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">BERANDA</a>
  <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') && !request('kategori') ? 'active' : '' }}">PRODUK</a>

  <div class="nv-drop" id="navDrop">
    <a href="{{ route('products.index') }}" class="nv-drop-trigger {{ request('kategori') ? 'active' : '' }}" onclick="return toggleNavDrop(event)">KATEGORI <span class="nv-caret">▾</span></a>
    @if(($navCategories ?? collect())->isNotEmpty())
    <div class="nv-menu">
      @foreach($navCategories as $cat)
        <a href="{{ route('products.index', ['kategori' => $cat->slug]) }}" class="{{ request('kategori') === $cat->slug ? 'on' : '' }}">
          <span class="nv-menu-ico">{{ $cat->icon }}</span> {{ $cat->name }}
        </a>
      @endforeach
    </div>
    @endif
  </div>

  <a href="{{ route('promo') }}" class="{{ request()->routeIs('promo') ? 'active' : '' }}">PROMO</a>
  <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">TENTANG KAMI</a>
  <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">KONTAK</a>
</div>
</nav>
<script>
// Dropdown kategori: klik untuk toggle (jalan di desktop & mobile/touch)
function toggleNavDrop(e){
  // di desktop lebar (>720px) biarkan link langsung ke /produk; dropdown via hover CSS
  if (window.innerWidth > 720) return true;
  e.preventDefault();
  var d = document.getElementById('navDrop');
  var menu = d.querySelector('.nv-menu');
  var willOpen = !d.classList.contains('open');
  d.classList.toggle('open');
  if (willOpen && menu){
    // posisikan tepat di bawah trigger (karena fixed)
    var r = e.currentTarget.getBoundingClientRect();
    menu.style.top = (r.bottom + 4) + 'px';
  }
  return false;
}
document.addEventListener('click', function(e){
  var d = document.getElementById('navDrop');
  if (d && !d.contains(e.target)) d.classList.remove('open');
});
</script>
