@extends('layouts.admin')
@section('title', 'Kategori')
@section('heading', 'Kategori')

@section('content')
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">
  <div class="panel">
    <div class="panel-hd"><h2>Daftar Kategori</h2></div>
    @if($categories->isEmpty())
      <div class="empty">Belum ada kategori.</div>
    @else
    <table>
      <thead><tr><th>Nama</th><th>Slug</th><th>Produk</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($categories as $c)
          <tr>
            <td>
              <form method="POST" action="{{ route('admin.categories.update', $c) }}" style="display:flex;gap:8px;align-items:center">@csrf @method('PUT')
                <input class="inp" type="text" name="name" value="{{ $c->name }}" style="max-width:180px">
                <input class="inp" type="text" name="icon" value="{{ $c->icon }}" placeholder="emoji" style="max-width:70px">
                <button class="btn btn-sm" type="submit">Update</button>
              </form>
            </td>
            <td style="color:var(--muted)">{{ $c->slug }}</td>
            <td>{{ $c->products_count }}</td>
            <td><span class="badge {{ $c->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirmDelete()">@csrf @method('DELETE')
                <button class="btn btn-sm btn-red" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  <div class="panel">
    <div class="panel-hd"><h2>Tambah Kategori</h2></div>
    <div style="padding:20px">
      <form method="POST" action="{{ route('admin.categories.store') }}">@csrf
        <div class="fld"><label>Nama Kategori</label><input class="inp" type="text" name="name" required></div>
        <div class="fld"><label>Ikon (emoji, opsional)</label><input class="inp" type="text" name="icon" placeholder="🔌"></div>
        <button class="btn btn-blue" type="submit" style="width:100%">+ Tambah</button>
      </form>
    </div>
  </div>
</div>
@endsection
