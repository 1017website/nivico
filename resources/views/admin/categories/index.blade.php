@extends('layouts.admin')
@section('title', 'Kategori')
@section('heading', 'Kategori')

@section('content')
@php
  $iconOptions = $iconOptions ?? config('category_icons', []);
  $iconClasses = collect($iconOptions)->pluck('class')->all();
@endphp

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
  <div class="panel">
    <div class="panel-hd"><h2>Daftar Kategori</h2></div>
    @if($categories->isEmpty())
      <div class="empty">Belum ada kategori.</div>
    @else
    <div class="table-wrap"><table>
      <thead><tr><th>Nama</th><th>Icon</th><th>Slug</th><th>Produk</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($categories as $c)
          <tr>
            <td style="min-width:250px">
              <form method="POST" id="cat-form-{{ $c->id }}" action="{{ route('admin.categories.update', $c) }}" class="cat-edit-form">@csrf @method('PUT')
                <input class="inp" type="text" name="name" value="{{ $c->name }}" required>
                <input type="hidden" name="is_active" value="0">
                <label class="cat-active"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)> Aktif</label>
                <button class="btn btn-sm" type="submit">Update</button>
              </form>
            </td>
            <td style="min-width:190px">
              <div class="cat-icon-row" form="cat-form-{{ $c->id }}">
                <span class="cat-icon-preview">{!! $c->iconHtml() !!}</span>
                <select class="inp cat-icon-select" name="icon" form="cat-form-{{ $c->id }}">
                  <option value="">Pilih icon</option>
                  @if($c->icon && !in_array($c->icon, $iconClasses, true))
                    <option value="{{ $c->icon }}" selected>Icon saat ini</option>
                  @endif
                  @foreach($iconOptions as $icon)
                    <option value="{{ $icon['class'] }}" data-icon="{{ $icon['class'] }}" @selected($c->icon === $icon['class'])>{{ $icon['label'] }}</option>
                  @endforeach
                </select>
              </div>
            </td>
            <td style="color:var(--muted)">{{ $c->slug }}</td>
            <td>{{ $c->products_count }}</td>
            <td><span class="badge {{ $c->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <div style="display:flex;gap:6px;align-items:center;justify-content:flex-end">
                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirmDelete()">@csrf @method('DELETE')
                  <button class="btn btn-sm btn-red" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table></div>
    @endif
  </div>

  <div class="panel">
    <div class="panel-hd"><h2>Tambah Kategori</h2></div>
    <div style="padding:20px">
      <form method="POST" action="{{ route('admin.categories.store') }}">@csrf
        <div class="fld"><label>Nama Kategori</label><input class="inp" type="text" name="name" required></div>

        <div class="fld">
          <label>Pilih Icon</label>
          <div class="cat-icon-picker">
            @foreach($iconOptions as $icon)
              <label class="cat-icon-choice" title="{{ $icon['label'] }}">
                <input type="radio" name="icon" value="{{ $icon['class'] }}" @checked($loop->first)>
                <span><i class="{{ $icon['class'] }}"></i></span>
                <small>{{ $icon['label'] }}</small>
              </label>
            @endforeach
          </div>
        </div>

        <button class="btn btn-blue" type="submit" style="width:100%"><i class="fa-solid fa-plus"></i> Tambah</button>
      </form>
    </div>
  </div>
</div>

@push('styles')
<style>
.cat-edit-form{display:grid;grid-template-columns:minmax(130px,1fr) auto auto;gap:8px;align-items:center}
.cat-active{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--muted);white-space:nowrap}
.cat-icon-row{display:flex;align-items:center;gap:9px}
.cat-icon-preview{width:36px;height:36px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;display:inline-flex;align-items:center;justify-content:center;font-size:18px;color:var(--blue);flex-shrink:0}
.cat-icon-preview svg{width:20px;height:20px}
.cat-icon-select{min-width:135px}
.cat-icon-picker{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;max-height:330px;overflow:auto;padding:2px}
.cat-icon-choice{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;min-height:74px;border:1px solid var(--border);border-radius:12px;background:#fff;cursor:pointer;transition:all .15s;padding:8px;text-align:center}
.cat-icon-choice input{position:absolute;opacity:0;pointer-events:none}
.cat-icon-choice span{width:34px;height:34px;border-radius:10px;background:#f1f5f9;color:#334155;display:flex;align-items:center;justify-content:center;font-size:17px;transition:all .15s}
.cat-icon-choice small{font-size:10px;line-height:1.15;color:var(--muted)}
.cat-icon-choice:hover{border-color:var(--blue);box-shadow:0 4px 14px rgba(37,99,235,.08)}
.cat-icon-choice:has(input:checked){border-color:var(--blue);background:#eff6ff;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
.cat-icon-choice:has(input:checked) span{background:var(--blue);color:#fff}
@media(max-width:980px){.ct [style*="grid-template-columns:1fr 340px"]{grid-template-columns:1fr !important}.cat-icon-picker{grid-template-columns:repeat(5,1fr)}}
@media(max-width:640px){.cat-edit-form{grid-template-columns:1fr}.cat-icon-picker{grid-template-columns:repeat(3,1fr)}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('change', function (event) {
  if (!event.target.classList.contains('cat-icon-select')) return;

  var select = event.target;
  var wrapper = select.closest('.cat-icon-row');
  var preview = wrapper ? wrapper.querySelector('.cat-icon-preview') : null;
  var selected = select.options[select.selectedIndex];
  var iconClass = selected ? selected.getAttribute('data-icon') : '';

  if (preview) {
    preview.innerHTML = iconClass ? '<i class="' + iconClass + '"></i>' : '<i class="fa-solid fa-tag"></i>';
  }

  if (select.form) {
    select.form.submit();
  }
});
</script>
@endpush
@endsection
