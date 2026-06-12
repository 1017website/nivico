@extends('layouts.admin')
@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="panel" style="max-width:840px">
  <div class="panel-hd"><h2>{{ $product->exists ? 'Edit: '.$product->name : 'Produk Baru' }}</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.products.index') }}">← Kembali</a></div>
  <div style="padding:24px">
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
      @csrf
      @if($product->exists) @method('PUT') @endif

      <div class="frm-grid">
        <div class="fld full"><label>Nama Produk</label><input class="inp" type="text" name="name" value="{{ old('name', $product->name) }}" required></div>

        <div class="fld"><label>Kategori</label>
          <select class="inp" name="category_id" required>
            <option value="">Pilih kategori</option>
            @foreach($categories as $c)
              <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="fld"><label>SKU</label><input class="inp" type="text" name="sku" value="{{ old('sku', $product->sku) }}" required></div>

        <div class="fld"><label>Harga (Rp)</label><input class="inp" type="number" name="price" value="{{ old('price', $product->price) }}" min="0" required></div>
        <div class="fld"><label>Harga Coret / Lama (Rp, opsional)</label><input class="inp" type="number" name="old_price" value="{{ old('old_price', $product->old_price) }}" min="0"></div>

        <div class="fld"><label>Stok</label><input class="inp" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required></div>
        <div class="fld"><label>Badge</label>
          <select class="inp" name="badge">
            <option value="">Tidak ada</option>
            <option value="NEW" @selected(old('badge', $product->badge)==='NEW')>NEW</option>
            <option value="HOT" @selected(old('badge', $product->badge)==='HOT')>HOT</option>
          </select>
        </div>

        <div class="fld"><label>Rating (0–5)</label><input class="inp" type="number" step="0.1" max="5" min="0" name="rating" value="{{ old('rating', $product->rating ?? 4.8) }}"></div>
        <div class="fld"><label>Jumlah Ulasan</label><input class="inp" type="number" name="rating_count" value="{{ old('rating_count', $product->rating_count ?? 0) }}" min="0"></div>

        <div class="fld full"><label>URL Gambar (atau upload di bawah)</label><input class="inp" type="text" name="image" value="{{ old('image', $product->image) }}" placeholder="https://..."></div>
        <div class="fld full"><label>Upload Gambar (opsional, menimpa URL)</label><input class="inp" type="file" name="image_file" accept="image/*"></div>

        <div class="fld full"><label>Deskripsi</label><textarea class="inp" name="description" rows="4">{{ old('description', $product->description) }}</textarea></div>

        <div class="fld"><label><input type="checkbox" name="is_flash_sale" value="1" @checked(old('is_flash_sale', $product->is_flash_sale))> Tampilkan di Flash Sale</label></div>
        <div class="fld"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))> Produk Aktif</label></div>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn btn-blue" type="submit">💾 Simpan</button>
        <a class="btn btn-gray" href="{{ route('admin.products.index') }}">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
