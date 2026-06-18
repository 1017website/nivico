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

        <div class="fld" id="fld-price"><label>Harga (Rp)</label><input class="inp" type="number" name="price" value="{{ old('price', $product->price) }}" min="0"></div>
        <div class="fld"><label>Harga Coret / Lama (Rp, opsional)</label><input class="inp" type="number" name="old_price" value="{{ old('old_price', $product->old_price) }}" min="0"></div>

        <div class="fld" id="fld-stock"><label>Stok</label><input class="inp" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0"></div>
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

      {{-- ── VARIAN ── --}}
      @php $hasVar = old('has_variants', $product->has_variants); @endphp
      <div style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border)">
        <label style="font-weight:600;display:flex;align-items:center;gap:8px">
          <input type="checkbox" name="has_variants" id="has-variants" value="1" @checked($hasVar)>
          Produk ini punya varian (mis. ukuran/warna, harga & stok per varian)
        </label>

        <div id="variants-box" style="margin-top:14px;{{ $hasVar ? '' : 'display:none' }}">
          <table style="width:100%;border-collapse:collapse;font-size:13px" id="variants-table">
            <thead>
              <tr style="text-align:left;color:var(--muted)">
                <th style="padding:6px 8px;width:26%">Nama Varian</th>
                <th style="padding:6px 8px;width:18%">SKU</th>
                <th style="padding:6px 8px;width:18%">Harga (Rp)</th>
                <th style="padding:6px 8px;width:16%">Harga Coret</th>
                <th style="padding:6px 8px;width:12%">Stok</th>
                <th style="padding:6px 8px;width:10%"></th>
              </tr>
            </thead>
            <tbody id="variants-body">
              @php
                $existing = old('variants', $product->exists ? $product->variants->map(fn($v)=>[
                  'id'=>$v->id,'name'=>$v->name,'sku'=>$v->sku,'price'=>$v->price,'old_price'=>$v->old_price,'stock'=>$v->stock,
                ])->all() : []);
              @endphp
              @foreach($existing as $i => $v)
                <tr class="var-row">
                  <td style="padding:4px"><input type="hidden" name="variants[{{ $i }}][id]" value="{{ $v['id'] ?? '' }}"><input class="inp" name="variants[{{ $i }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="mis. 40w"></td>
                  <td style="padding:4px"><input class="inp" name="variants[{{ $i }}][sku]" value="{{ $v['sku'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][price]" value="{{ $v['price'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][old_price]" value="{{ $v['old_price'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][stock]" value="{{ $v['stock'] ?? '' }}"></td>
                  <td style="padding:4px"><button type="button" class="btn btn-sm btn-gray var-del">Hapus</button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <button type="button" class="btn btn-sm btn-gray" id="add-variant" style="margin-top:8px">+ Tambah Varian</button>
          <p style="font-size:12px;color:var(--muted);margin-top:6px">Saat mode varian aktif, harga & stok produk diabaikan — yang dipakai harga & stok per varian.</p>
        </div>
      </div>

      <template id="variant-row-tpl">
        <tr class="var-row">
          <td style="padding:4px"><input type="hidden" name="variants[__IDX__][id]" value=""><input class="inp" name="variants[__IDX__][name]" placeholder="mis. 40w"></td>
          <td style="padding:4px"><input class="inp" name="variants[__IDX__][sku]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][price]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][old_price]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][stock]"></td>
          <td style="padding:4px"><button type="button" class="btn btn-sm btn-gray var-del">Hapus</button></td>
        </tr>
      </template>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn btn-blue" type="submit">💾 Simpan</button>
        <a class="btn btn-gray" href="{{ route('admin.products.index') }}">Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  var chk = document.getElementById('has-variants');
  var box = document.getElementById('variants-box');
  var body = document.getElementById('variants-body');
  var tpl = document.getElementById('variant-row-tpl');
  var addBtn = document.getElementById('add-variant');
  var fldPrice = document.getElementById('fld-price');
  var fldStock = document.getElementById('fld-stock');
  var idx = body ? body.querySelectorAll('.var-row').length : 0;

  function toggle() {
    var on = chk.checked;
    box.style.display = on ? '' : 'none';
    if (fldPrice) fldPrice.style.display = on ? 'none' : '';
    if (fldStock) fldStock.style.display = on ? 'none' : '';
    // bila aktif tapi belum ada baris, tambahkan satu
    if (on && body && body.querySelectorAll('.var-row').length === 0) addRow();
  }

  function addRow() {
    var html = tpl.innerHTML.replace(/__IDX__/g, idx++);
    var tr = document.createElement('tbody');
    tr.innerHTML = html.trim();
    var row = tr.firstChild;
    body.appendChild(row);
  }

  if (chk) chk.addEventListener('change', toggle);
  if (addBtn) addBtn.addEventListener('click', addRow);
  if (body) body.addEventListener('click', function (e) {
    if (e.target.classList.contains('var-del')) {
      e.target.closest('.var-row').remove();
    }
  });

  // sinkronkan tampilan awal
  toggle();
})();
</script>
@endsection
