@extends('layouts.app')
@section('title', 'Semua Produk — NIVICO Electronic Mart')

@section('content')
<div class="sw" style="margin-top:14px"><div class="sec">
  <div class="bc" style="padding-top:16px">
    <a href="{{ route('home') }}">Beranda</a> › <span>Produk</span>
  </div>

  <div class="sh">
    <h2>
      @if(request('q')) Hasil: "{{ request('q') }}"
      @elseif(request('kategori')) {{ optional($categories->firstWhere('slug', request('kategori')))->name ?? 'Produk' }}
      @else SEMUA PRODUK @endif
    </h2>
    <form method="GET" style="display:flex;gap:8px;align-items:center">
      @foreach(request()->except('sort') as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
      @endforeach
      <select name="sort" onchange="this.form.submit()" style="border:1px solid var(--border);border-radius:6px;padding:7px 10px;font-size:12.5px;cursor:pointer">
        <option value="baru"     @selected($sort==='baru')>Terbaru</option>
        <option value="murah"    @selected($sort==='murah')>Harga Termurah</option>
        <option value="mahal"    @selected($sort==='mahal')>Harga Tertinggi</option>
        <option value="terlaris" @selected($sort==='terlaris')>Terlaris</option>
      </select>
    </form>
  </div>

  {{-- chip kategori --}}
  <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:14px;scrollbar-width:none">
    <a href="{{ route('products.index') }}" class="ptab {{ !request('kategori') ? 'on' : '' }}" style="white-space:nowrap">Semua</a>
    @foreach($categories as $cat)
      <a href="{{ route('products.index', ['kategori' => $cat->slug]) }}" class="ptab {{ request('kategori')===$cat->slug ? 'on' : '' }}" style="white-space:nowrap">{{ $cat->name }}</a>
    @endforeach
  </div>

  @if($products->isEmpty())
    <div class="empty-cart" style="background:#fff;border:1px solid var(--border);border-radius:10px;margin-bottom:20px">
      <h3>Produk tidak ditemukan</h3>
      <p>Coba kata kunci atau kategori lain.</p>
    </div>
  @else
    <div class="pg">
      @foreach($products as $p)
        <x-product-card :product="$p" />
      @endforeach
    </div>
    <div style="padding:8px 0 24px">{{ $products->links('vendor.pagination.nivico') }}</div>
  @endif
</div></div>
@endsection
