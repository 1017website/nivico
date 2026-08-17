@extends('layouts.admin')
@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="panel" style="max-width:1180px">
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
        <div class="fld"><label>Berat Produk / Default (gram) <span style="color:var(--red)">*</span></label><input class="inp" id="product-weight" type="number" name="weight" value="{{ old('weight', $product->weight ?? 1000) }}" min="1" max="1000000" required><small style="color:var(--muted)">Dipakai untuk produk tanpa varian dan sebagai nilai awal varian baru.</small></div>
        <div class="fld">
          <label>Dimensi Kemasan / Default (cm)</label>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <input class="inp" id="product-length" type="number" name="length" value="{{ old('length', $product->length) }}" min="1" max="1000" placeholder="Panjang" aria-label="Panjang kemasan">
            <input class="inp" id="product-width" type="number" name="width" value="{{ old('width', $product->width) }}" min="1" max="1000" placeholder="Lebar" aria-label="Lebar kemasan">
            <input class="inp" id="product-height" type="number" name="height" value="{{ old('height', $product->height) }}" min="1" max="1000" placeholder="Tinggi" aria-label="Tinggi kemasan">
          </div>
          <small style="color:var(--muted)">Isi lengkap panjang × lebar × tinggi. Kosongkan semuanya bila tidak digunakan.</small>
        </div>
        <div class="fld"><label>Badge</label>
          <select class="inp" name="badge">
            <option value="">Tidak ada</option>
            <option value="NEW" @selected(old('badge', $product->badge)==='NEW')>NEW</option>
            <option value="HOT" @selected(old('badge', $product->badge)==='HOT')>HOT</option>
          </select>
        </div>

        <div class="fld"><label>Rating (0–5)</label><input class="inp" type="number" step="0.1" max="5" min="0" name="rating" value="{{ old('rating', $product->rating ?? 4.8) }}"></div>
        <div class="fld"><label>Jumlah Ulasan</label><input class="inp" type="number" name="rating_count" value="{{ old('rating_count', $product->rating_count ?? 0) }}" min="0"></div>

        @php
          $currentGallery = $product->exists ? $product->images : collect();
          $currentImageCount = ($product->image ? 1 : 0) + $currentGallery->count();
        @endphp
        <div class="fld full">
          <label>URL Gambar Utama (opsional)</label>
          <input class="inp" type="text" name="image" value="{{ old('image', $product->image) }}" placeholder="https://...">
        </div>

        @if($currentImageCount > 0)
          <div class="fld full">
            <label>Gambar Saat Ini ({{ $currentImageCount }}/10)</label>
            <div id="current-images" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;margin-top:4px">
              @if($product->image)
                <label class="product-image-item" style="border:1px solid var(--border);border-radius:8px;padding:8px;cursor:pointer">
                  <img src="{{ $product->image }}" alt="Gambar utama" style="width:100%;height:90px;object-fit:cover;border-radius:6px" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">
                  <span style="display:block;font-size:11px;font-weight:700;margin-top:6px">Gambar utama</span>
                  <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--red);margin-top:4px"><input class="image-remove-check" type="checkbox" name="remove_primary_image" value="1"> Hapus</span>
                </label>
              @endif
              @foreach($currentGallery as $galleryImage)
                <label class="product-image-item" style="border:1px solid var(--border);border-radius:8px;padding:8px;cursor:pointer">
                  <img src="{{ $galleryImage->path }}" alt="Gambar galeri" style="width:100%;height:90px;object-fit:cover;border-radius:6px" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">
                  <span style="display:block;font-size:11px;font-weight:700;margin-top:6px">Galeri {{ $loop->iteration }}</span>
                  <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--red);margin-top:4px"><input class="image-remove-check" type="checkbox" name="remove_images[]" value="{{ $galleryImage->id }}"> Hapus</span>
                </label>
              @endforeach
            </div>
          </div>
        @endif

        <div class="fld full">
          <label>Upload Gambar (maksimal 10 gambar total)</label>
          <input class="inp" id="image-files" type="file" name="image_files[]" accept="image/jpeg,image/png,image/webp" multiple data-existing-count="{{ $currentImageCount }}">
          <small id="image-help" style="color:var(--muted)">Pilih beberapa file sekaligus. Format JPG, PNG, atau WebP; maksimal 5 MB per gambar.</small>
          <div id="new-image-preview" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;margin-top:10px"></div>
        </div>

        <div class="fld full"><label>Deskripsi Produk <span style="color:var(--red)">*</span></label><textarea class="inp" name="description" rows="5" minlength="20" maxlength="5000" required placeholder="Jelaskan fungsi, spesifikasi utama, isi paket, dan penggunaan produk.">{{ old('description', $product->description) }}</textarea><small style="color:var(--muted)">Wajib diisi agar pelanggan dan penyedia pembayaran dapat memahami produk yang dijual.</small></div>

        <div class="fld"><label><input type="checkbox" name="is_flash_sale" value="1" @checked(old('is_flash_sale', $product->is_flash_sale))> Tampilkan di Flash Sale</label></div>
        <div class="fld"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))> Produk Aktif</label></div>
      </div>

      {{-- ── VARIAN ── --}}
      @php $hasVar = old('has_variants', $product->has_variants); @endphp
      <div style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border)">
        <label style="font-weight:600;display:flex;align-items:center;gap:8px">
          <input type="checkbox" name="has_variants" id="has-variants" value="1" @checked($hasVar)>
          Produk ini punya varian (harga, stok, berat & dimensi per varian)
        </label>

        <div id="variants-box" style="margin-top:14px;{{ $hasVar ? '' : 'display:none' }}">
          <div style="overflow-x:auto;padding-bottom:4px">
          <table style="width:100%;min-width:1180px;border-collapse:collapse;font-size:13px" id="variants-table">
            <thead>
              <tr style="text-align:left;color:var(--muted)">
                <th style="padding:6px 8px">Nama Varian</th>
                <th style="padding:6px 8px">SKU</th>
                <th style="padding:6px 8px">Harga (Rp)</th>
                <th style="padding:6px 8px">Harga Coret</th>
                <th style="padding:6px 8px">Stok</th>
                <th style="padding:6px 8px">Berat (g)</th>
                <th style="padding:6px 8px">Panjang (cm)</th>
                <th style="padding:6px 8px">Lebar (cm)</th>
                <th style="padding:6px 8px">Tinggi (cm)</th>
                <th style="padding:6px 8px"></th>
              </tr>
            </thead>
            <tbody id="variants-body">
              @php
                $existing = old('variants', $product->exists ? $product->variants->map(fn($v)=>[
                  'id'=>$v->id,'name'=>$v->name,'sku'=>$v->sku,'price'=>$v->price,'old_price'=>$v->old_price,'stock'=>$v->stock,
                  'weight'=>$v->weight ?: $product->weight,
                  'length'=>$v->length ?: $product->length,'width'=>$v->width ?: $product->width,'height'=>$v->height ?: $product->height,
                ])->all() : []);
              @endphp
              @foreach($existing as $i => $v)
                <tr class="var-row">
                  <td style="padding:4px"><input type="hidden" name="variants[{{ $i }}][id]" value="{{ $v['id'] ?? '' }}"><input class="inp" name="variants[{{ $i }}][name]" value="{{ $v['name'] ?? '' }}" placeholder="mis. 40w"></td>
                  <td style="padding:4px"><input class="inp" name="variants[{{ $i }}][sku]" value="{{ $v['sku'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][price]" value="{{ $v['price'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][old_price]" value="{{ $v['old_price'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[{{ $i }}][stock]" value="{{ $v['stock'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="1" max="1000000" name="variants[{{ $i }}][weight]" value="{{ $v['weight'] ?? old('weight', $product->weight ?? 1000) }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="1" max="1000" name="variants[{{ $i }}][length]" value="{{ $v['length'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="1" max="1000" name="variants[{{ $i }}][width]" value="{{ $v['width'] ?? '' }}"></td>
                  <td style="padding:4px"><input class="inp" type="number" min="1" max="1000" name="variants[{{ $i }}][height]" value="{{ $v['height'] ?? '' }}"></td>
                  <td style="padding:4px"><button type="button" class="btn btn-sm btn-gray var-del">Hapus</button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
          </div>
          <button type="button" class="btn btn-sm btn-gray" id="add-variant" style="margin-top:8px">+ Tambah Varian</button>
          <p style="font-size:12px;color:var(--muted);margin-top:6px">Saat mode varian aktif, harga, stok, berat, dan dimensi pengiriman dibaca dari varian yang dipilih pelanggan.</p>
        </div>
      </div>

      <template id="variant-row-tpl">
        <tr class="var-row">
          <td style="padding:4px"><input type="hidden" name="variants[__IDX__][id]" value=""><input class="inp" name="variants[__IDX__][name]" placeholder="mis. 40w"></td>
          <td style="padding:4px"><input class="inp" name="variants[__IDX__][sku]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][price]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][old_price]"></td>
          <td style="padding:4px"><input class="inp" type="number" min="0" name="variants[__IDX__][stock]"></td>
          <td style="padding:4px"><input class="inp variant-weight" type="number" min="1" max="1000000" name="variants[__IDX__][weight]"></td>
          <td style="padding:4px"><input class="inp variant-length" type="number" min="1" max="1000" name="variants[__IDX__][length]"></td>
          <td style="padding:4px"><input class="inp variant-width" type="number" min="1" max="1000" name="variants[__IDX__][width]"></td>
          <td style="padding:4px"><input class="inp variant-height" type="number" min="1" max="1000" name="variants[__IDX__][height]"></td>
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
  var productWeight = document.getElementById('product-weight');
  var productLength = document.getElementById('product-length');
  var productWidth = document.getElementById('product-width');
  var productHeight = document.getElementById('product-height');
  var imageFiles = document.getElementById('image-files');
  var imageHelp = document.getElementById('image-help');
  var imagePreview = document.getElementById('new-image-preview');
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
    row.querySelector('.variant-weight').value = productWeight ? productWeight.value : '1000';
    row.querySelector('.variant-length').value = productLength ? productLength.value : '';
    row.querySelector('.variant-width').value = productWidth ? productWidth.value : '';
    row.querySelector('.variant-height').value = productHeight ? productHeight.value : '';
  }

  if (chk) chk.addEventListener('change', toggle);
  if (addBtn) addBtn.addEventListener('click', addRow);
  if (body) body.addEventListener('click', function (e) {
    if (e.target.classList.contains('var-del')) {
      e.target.closest('.var-row').remove();
    }
  });

  function remainingExistingImages() {
    var existing = imageFiles ? parseInt(imageFiles.dataset.existingCount || '0', 10) : 0;
    var removed = document.querySelectorAll('.image-remove-check:checked').length;
    return Math.max(0, existing - removed);
  }

  function updateImageHelp(selected) {
    if (!imageHelp) return;
    var existing = remainingExistingImages();
    imageHelp.textContent = existing + ' gambar tersimpan + ' + selected + ' gambar baru (maksimal 10). Format JPG, PNG, atau WebP; maksimal 5 MB per gambar.';
  }

  if (imageFiles) imageFiles.addEventListener('change', function () {
    var files = Array.from(this.files || []);
    var available = 10 - remainingExistingImages();

    if (files.length > available) {
      alert('Maksimal 10 gambar per produk. Anda hanya dapat menambah ' + available + ' gambar lagi.');
      this.value = '';
      files = [];
    }

    imagePreview.innerHTML = '';
    files.forEach(function (file, index) {
      var item = document.createElement('div');
      item.style.cssText = 'border:1px solid var(--border);border-radius:8px;padding:6px;font-size:11px;overflow:hidden';
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = 'Preview gambar ' + (index + 1);
      img.style.cssText = 'width:100%;height:76px;object-fit:cover;border-radius:5px;margin-bottom:5px';
      img.onload = function () { URL.revokeObjectURL(img.src); };
      var name = document.createElement('div');
      name.textContent = file.name;
      name.style.cssText = 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis';
      item.appendChild(img);
      item.appendChild(name);
      imagePreview.appendChild(item);
    });
    updateImageHelp(files.length);
  });

  document.querySelectorAll('.image-remove-check').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
      updateImageHelp(imageFiles && imageFiles.files ? imageFiles.files.length : 0);
      var item = checkbox.closest('.product-image-item');
      if (item) item.style.opacity = checkbox.checked ? '.45' : '1';
    });
  });

  // sinkronkan tampilan awal
  toggle();
  updateImageHelp(0);
})();
</script>
@endsection
