@extends('layouts.admin')
@section('title', 'Produk')
@section('heading', 'Produk')

@section('content')
<div class="toolbar">
  <form method="GET" action="{{ route('admin.products.index') }}">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU produk...">
    <button class="btn btn-gray" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a class="btn btn-gray" href="{{ route('admin.products.import') }}" style="margin-left:auto"><i class="fa-solid fa-file-import"></i> Import Shopee</a>
  <a class="btn btn-blue" href="{{ route('admin.products.create') }}"><i class="fa-solid fa-plus"></i> Tambah Produk</a>
</div>

<div class="panel">
  @if($products->isEmpty())
    <div class="empty">Belum ada produk. Klik "Tambah Produk" untuk membuat.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Terjual</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($products as $p)
        <tr>
          <td style="display:flex;align-items:center;gap:10px">
            <img class="thumb" src="{{ $p->image ?: asset('images/placeholder-product.svg') }}" alt="" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">
            <div><div style="font-weight:600">{{ $p->name }}@if($p->has_variants) <span class="badge b-completed" style="font-size:10px">Varian</span>@endif</div><div style="font-size:11.5px;color:var(--muted)">{{ $p->sku }}</div></div>
          </td>
          <td>{{ $p->category->name ?? '-' }}</td>
          <td style="font-weight:600">@if($p->has_variants && $p->hasPriceRange())Rp{{ number_format($p->min_price, 0, ',', '.') }}+@else Rp{{ number_format($p->min_price, 0, ',', '.') }}@endif</td>
          <td><span class="badge {{ $p->total_stock < 10 ? 'b-cancelled' : 'b-completed' }}">{{ $p->total_stock }}</span></td>
          <td>{{ $p->sold }}</td>
          <td><span class="badge {{ $p->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm btn-blue" href="{{ route('admin.products.edit', $p) }}"><i class="fa-solid fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="pag">{{ $products->links() }}</div>
  @endif
</div>
@endsection
