@extends('layouts.admin')
@section('title', 'Import Produk Shopee')
@section('heading', 'Import Produk dari Shopee')

@section('content')

<div class="toolbar">
  <a class="btn btn-gray" href="{{ route('admin.products.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Produk</a>
</div>

{{-- ── LANGKAH 1: UPLOAD ── --}}
@if(! $preview)
<div class="panel">
  <div class="panel-hd"><h2><i class="fa-solid fa-file-import"></i> Unggah File Export Shopee</h2></div>
  <div style="padding:20px">

    <div class="imp-help">
      <strong><i class="fa-solid fa-circle-info"></i> Cara mendapatkan file:</strong>
      <ol>
        <li>Buka <b>Seller Centre Shopee</b> &rarr; <b>Produk Saya</b> &rarr; <b>Pengaturan Massal</b> (Mass Edit).</li>
        <li>Pilih <b>Ekspor</b> untuk mengunduh data produk.</li>
        <li>Jika file berformat <b>.xlsx</b>, buka di Excel / Google Sheets lalu <b>Save As / Download as CSV</b>.</li>
        <li>Unggah file <b>.csv</b> tersebut di bawah ini.</li>
      </ol>
      <small>Mendukung produk bervarian: tiap baris variasi akan otomatis menjadi varian produk. Kolom yang dikenali: Nama Produk, Deskripsi, SKU, Nama Variasi, Harga, Stok, Berat, Foto, Kategori.</small>
    </div>

    <form method="POST" action="{{ route('admin.products.import.preview') }}" enctype="multipart/form-data" style="margin-top:18px">
      @csrf
      <div class="fld">
        <label>File CSV Shopee</label>
        <input class="inp" type="file" name="file" accept=".csv,text/csv" required>
        <small style="color:var(--muted)">Maksimal 10 MB. Format .csv.</small>
      </div>
      <button class="btn btn-blue" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Pratinjau Data</button>
    </form>

  </div>
</div>

{{-- ── LANGKAH 2: PREVIEW + KONFIRMASI ── --}}
@else
  @php $rows = $preview['rows'] ?? []; @endphp

  @if(! empty($preview['errors']) && empty($rows))
    <div class="panel"><div style="padding:20px">
      <div class="imp-alert err"><i class="fa-solid fa-triangle-exclamation"></i> {{ implode(' ', $preview['errors']) }}</div>
      <a class="btn btn-gray" href="{{ route('admin.products.import') }}" style="margin-top:14px"><i class="fa-solid fa-rotate-left"></i> Unggah Ulang</a>
    </div></div>
  @else
  <form method="POST" action="{{ route('admin.products.import.execute') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="panel">
      <div class="panel-hd"><h2><i class="fa-solid fa-list-check"></i> Pratinjau — {{ count($rows) }} baris terdeteksi</h2></div>
      <div style="padding:20px">

        <div class="imp-grid">
          <div class="fld">
            <label>Kategori Default <small>(bila kolom kategori kosong)</small></label>
            <select class="inp" name="category_id">
              <option value="">— Buat dari kolom kategori file —</option>
              @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="fld">
            <label>Opsi</label>
            <label class="imp-check"><input type="hidden" name="default_active" value="0"><input type="checkbox" name="default_active" value="1" checked> Aktifkan produk setelah diimpor</label>
            <label class="imp-check"><input type="hidden" name="update_existing" value="0"><input type="checkbox" name="update_existing" value="1" checked> Perbarui produk bila SKU sudah ada</label>
          </div>
        </div>

        <div class="table-wrap" style="margin-top:16px">
          <table>
            <thead><tr><th>#</th><th>Nama Produk</th><th>SKU</th><th>Variasi</th><th>Harga</th><th>Stok</th><th>Berat</th><th>Kategori</th></tr></thead>
            <tbody>
              @foreach(array_slice($rows, 0, 100) as $i => $r)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td style="font-weight:600">{{ $r['name'] ?: '—' }}</td>
                  <td style="font-size:12px;color:var(--muted)">{{ $r['sku'] ?: '(auto)' }}</td>
                  <td>{{ $r['variant_name'] ?: '—' }}</td>
                  <td>Rp{{ number_format($r['price'], 0, ',', '.') }}</td>
                  <td>{{ $r['stock'] }}</td>
                  <td>{{ $r['weight'] ? $r['weight'].' g' : '—' }}</td>
                  <td>{{ $r['category'] ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @if(count($rows) > 100)
          <small style="color:var(--muted);display:block;margin-top:8px">Menampilkan 100 baris pertama dari {{ count($rows) }}. Semua baris akan diimpor.</small>
        @endif

        @if(! empty($preview['errors']))
          <div class="imp-alert warn" style="margin-top:14px"><i class="fa-solid fa-circle-exclamation"></i> {{ implode(' ', $preview['errors']) }}</div>
        @endif

        <div style="display:flex;gap:10px;margin-top:18px">
          <button class="btn btn-blue" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Jalankan Import</button>
          <a class="btn btn-gray" href="{{ route('admin.products.import') }}"><i class="fa-solid fa-xmark"></i> Batal</a>
        </div>

      </div>
    </div>
  </form>
  @endif
@endif

@push('styles')
<style>
.imp-help{background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:16px 18px;font-size:13.5px;line-height:1.6}
.imp-help ol{margin:10px 0 10px 18px;display:flex;flex-direction:column;gap:4px}
.imp-help small{display:block;margin-top:10px;color:var(--muted)}
.imp-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.imp-check{display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:8px;cursor:pointer}
.imp-alert{padding:11px 15px;border-radius:10px;font-size:13px;font-weight:500}
.imp-alert.err{background:#fee2e2;color:#b91c1c}
.imp-alert.warn{background:#fef3c7;color:#92400e}
.fld{margin-bottom:14px}
.fld>label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
@media(max-width:640px){.imp-grid{grid-template-columns:1fr}}
</style>
@endpush
@endsection
