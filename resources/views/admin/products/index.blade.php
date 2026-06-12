@extends('layouts.admin')
@section('title', 'Produk')
@section('heading', 'Produk')

@section('content')
<div class="toolbar">
  <form method="GET" action="{{ route('admin.products.index') }}">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU produk...">
    <button class="btn" type="submit">Cari</button>
  </form>
  <a class="btn btn-blue" href="{{ route('admin.products.create') }}" style="margin-left:auto">+ Tambah Produk</a>
</div>

<div class="panel">
  @if($products->isEmpty())
    <div class="empty">Belum ada produk. Klik "Tambah Produk" untuk membuat.</div>
  @else
  <table>
    <thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Terjual</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($products as $p)
        <tr>
          <td style="display:flex;align-items:center;gap:10px">
            <img class="thumb" src="{{ $p->image }}" alt="">
            <div><div style="font-weight:600">{{ $p->name }}</div><div style="font-size:11.5px;color:var(--muted)">{{ $p->sku }}</div></div>
          </td>
          <td>{{ $p->category->name ?? '-' }}</td>
          <td style="font-weight:600">Rp{{ number_format($p->price, 0, ',', '.') }}</td>
          <td><span class="badge {{ $p->stock < 10 ? 'b-cancelled' : 'b-completed' }}">{{ $p->stock }}</span></td>
          <td>{{ $p->sold }}</td>
          <td><span class="badge {{ $p->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm btn-blue" href="{{ route('admin.products.edit', $p) }}">Edit</a>
            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div class="pag">{{ $products->links() }}</div>
  @endif
</div>
@endsection
