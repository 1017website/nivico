@extends('layouts.admin')
@section('title', 'Edit SEO')
@section('heading', 'Edit SEO — '.$label)

@section('content')
<div class="panel" style="max-width:720px">
  <div class="panel-hd"><h2>{{ $label }}</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.seo.index') }}">← Kembali</a></div>
  <div style="padding:24px">
    <form method="POST" action="{{ route('admin.seo.update', $pageKey) }}" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div class="fld"><label>Meta Title</label><input class="inp" type="text" name="title" value="{{ old('title', $setting->title) }}" maxlength="160" placeholder="Judul untuk tab browser & hasil pencarian"></div>
      <div class="fld"><label>Meta Description</label><textarea class="inp" name="meta_description" rows="2" maxlength="500" placeholder="Ringkasan halaman (maks. ±160 karakter ideal)">{{ old('meta_description', $setting->meta_description) }}</textarea></div>
      <div class="fld"><label>Meta Keywords</label><input class="inp" type="text" name="meta_keywords" value="{{ old('meta_keywords', $setting->meta_keywords) }}" placeholder="kata kunci, dipisah, koma"></div>
      <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">
      <div class="fld"><label>OG Title (untuk share medsos)</label><input class="inp" type="text" name="og_title" value="{{ old('og_title', $setting->og_title) }}" maxlength="160"></div>
      <div class="fld"><label>OG Description</label><textarea class="inp" name="og_description" rows="2" maxlength="500">{{ old('og_description', $setting->og_description) }}</textarea></div>
      <div class="fld"><label>OG Image URL</label><input class="inp" type="text" name="og_image" value="{{ old('og_image', $setting->og_image) }}" placeholder="https://..."></div>
      <div class="fld"><label>Upload OG Image (opsional)</label><input class="inp" type="file" name="og_image_file" accept="image/*"></div>
      @if($setting->og_image)<img src="{{ $setting->og_image }}" style="max-width:200px;border-radius:8px;border:1px solid var(--border);margin-bottom:14px">@endif
      <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">
      <div class="fld"><label>Canonical URL (opsional)</label><input class="inp" type="text" name="canonical_url" value="{{ old('canonical_url', $setting->canonical_url) }}" placeholder="https://nivico.id/..."></div>
      <div class="fld"><label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="noindex" value="1" @checked(old('noindex', $setting->noindex))> Sembunyikan dari mesin pencari (noindex)</label></div>
      <div style="display:flex;gap:10px"><button class="btn btn-blue" type="submit">💾 Simpan</button><a class="btn btn-gray" href="{{ route('admin.seo.index') }}">Batal</a></div>
    </form>
  </div>
</div>
@endsection
