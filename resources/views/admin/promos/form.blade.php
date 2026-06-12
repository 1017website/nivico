@extends('layouts.admin')
@section('title', $promo->exists ? 'Edit Promo' : 'Tambah Promo')
@section('heading', $promo->exists ? 'Edit Promo' : 'Tambah Promo')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-hd"><h2>{{ $promo->exists ? 'Edit: '.$promo->code : 'Promo Baru' }}</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.promos.index') }}">← Kembali</a></div>
  <div style="padding:24px">
    <form method="POST" action="{{ $promo->exists ? route('admin.promos.update', $promo) : route('admin.promos.store') }}">
      @csrf
      @if($promo->exists) @method('PUT') @endif

      <div class="frm-grid">
        <div class="fld"><label>Kode Promo</label><input class="inp" type="text" name="code" value="{{ old('code', $promo->code) }}" required style="text-transform:uppercase"></div>
        <div class="fld"><label>Badge</label>
          <select class="inp" name="badge">
            <option value="">Tidak ada</option>
            @foreach(['Flash Sale','Voucher','Gratis Ongkir','Cashback','Member'] as $b)
              <option @selected(old('badge', $promo->badge)===$b)>{{ $b }}</option>
            @endforeach
          </select>
        </div>

        <div class="fld full"><label>Judul</label><input class="inp" type="text" name="title" value="{{ old('title', $promo->title) }}" required></div>
        <div class="fld full"><label>Deskripsi</label><textarea class="inp" name="description" rows="2">{{ old('description', $promo->description) }}</textarea></div>

        <div class="fld"><label>Tipe</label>
          <select class="inp" name="type" required>
            <option value="fixed" @selected(old('type', $promo->type)==='fixed')>Potongan Tetap (Rp)</option>
            <option value="percent" @selected(old('type', $promo->type)==='percent')>Persen (%)</option>
            <option value="free_shipping" @selected(old('type', $promo->type)==='free_shipping')>Gratis Ongkir</option>
          </select>
        </div>
        <div class="fld"><label>Nilai (Rp / %)</label><input class="inp" type="number" name="value" value="{{ old('value', $promo->value ?? 0) }}" min="0"></div>

        <div class="fld"><label>Maks. Diskon (Rp, untuk tipe persen)</label><input class="inp" type="number" name="max_discount" value="{{ old('max_discount', $promo->max_discount) }}" min="0"></div>
        <div class="fld"><label>Min. Belanja (Rp)</label><input class="inp" type="number" name="min_purchase" value="{{ old('min_purchase', $promo->min_purchase ?? 0) }}" min="0"></div>

        <div class="fld"><label>Berlaku Sampai (opsional)</label><input class="inp" type="date" name="expires_at" value="{{ old('expires_at', optional($promo->expires_at)->format('Y-m-d')) }}"></div>
        <div class="fld"><label>URL Gambar (opsional)</label><input class="inp" type="text" name="image" value="{{ old('image', $promo->image) }}" placeholder="https://..."></div>

        <div class="fld full"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promo->exists ? $promo->is_active : true))> Promo Aktif</label></div>
      </div>

      <div style="display:flex;gap:10px">
        <button class="btn btn-blue" type="submit">💾 Simpan</button>
        <a class="btn btn-gray" href="{{ route('admin.promos.index') }}">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
