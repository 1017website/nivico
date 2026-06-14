@extends('layouts.admin')
@section('title', 'Konten Web')
@section('heading', 'Konten Web')

@section('content')

{{-- TABS --}}
<div class="content-tabs">
  @foreach($tabs as $k => $label)
    <a href="{{ route('admin.content.index', ['tab' => $k]) }}" class="ctab {{ $tab === $k ? 'on' : '' }}">{{ $label }}</a>
  @endforeach
</div>

<form method="POST" action="{{ route('admin.content.update', $tab) }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div class="panel">
    <div class="panel-hd"><h2>{{ $tabs[$tab] }}</h2><button class="btn btn-blue" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan</button></div>
    <div style="padding:20px">

      @forelse($settings as $s)
        @php $val = $s->castValue(); @endphp

        {{-- ── JSON REPEATER ── --}}
        @if($s->type === 'json')
          <div class="fld">
            <label style="font-weight:700;font-size:14px">{{ $s->label }}</label>
            @php
              // tentukan field per item berdasarkan key
              $fieldDefs = match($s->key) {
                'hero.slides'   => ['title1'=>'Judul Baris 1','title2'=>'Judul Baris 2','desc'=>'Deskripsi','image'=>'URL Gambar','cta_text'=>'Teks Tombol','cta_link'=>'Link Tombol'],
                'hero.perks'    => ['t1'=>'Baris 1','t2'=>'Baris 2'],
                'banner.promos' => ['tag'=>'Tag','title'=>'Judul (boleh <br>)','btn'=>'Teks Tombol','link'=>'Link','image'=>'URL Gambar'],
                default         => [],
              };
              $rows = is_array($val) ? $val : [];
              if (empty($rows)) $rows = [[]]; // minimal 1 baris kosong
            @endphp

            <div class="repeater" data-key="{{ $s->key }}">
              @foreach($rows as $i => $row)
                <div class="rep-item">
                  <div class="rep-head"><span>#{{ $i + 1 }}</span><button type="button" class="rep-del" onclick="repDel(this)"><i class="fa-solid fa-trash"></i></button></div>
                  <div class="rep-grid">
                    @foreach($fieldDefs as $fk => $flabel)
                      <div class="fld">
                        <label>{{ $flabel }}</label>
                        @if($fk === 'desc' || $fk === 'title')
                          <textarea class="inp" rows="2" name="json[{{ $s->key }}][{{ $i }}][{{ $fk }}]">{{ $row[$fk] ?? '' }}</textarea>
                        @else
                          <input class="inp" type="text" name="json[{{ $s->key }}][{{ $i }}][{{ $fk }}]" value="{{ $row[$fk] ?? '' }}">
                        @endif
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>

            <template id="tpl-{{ $s->key }}">
              <div class="rep-item">
                <div class="rep-head"><span>#baru</span><button type="button" class="rep-del" onclick="repDel(this)"><i class="fa-solid fa-trash"></i></button></div>
                <div class="rep-grid">
                  @foreach($fieldDefs as $fk => $flabel)
                    <div class="fld">
                      <label>{{ $flabel }}</label>
                      @if($fk === 'desc' || $fk === 'title')
                        <textarea class="inp" rows="2" data-name="json[{{ $s->key }}][__IDX__][{{ $fk }}]"></textarea>
                      @else
                        <input class="inp" type="text" data-name="json[{{ $s->key }}][__IDX__][{{ $fk }}]">
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            </template>

            <button type="button" class="btn btn-ghost btn-sm" onclick="repAdd('{{ $s->key }}')"><i class="fa-solid fa-plus"></i> Tambah</button>
          </div>
          <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">

        {{-- ── IMAGE ── --}}
        @elseif($s->type === 'image')
          <div class="fld">
            <label>{{ $s->label }}</label>
            @if($val)<div style="margin-bottom:8px"><img src="{{ $val }}" alt="" style="max-height:60px;border:1px solid var(--border);border-radius:8px;padding:4px;background:#fff"></div>@endif
            <input class="inp" type="text" name="val[{{ $s->key }}]" value="{{ $val }}" placeholder="URL gambar (atau upload di bawah)">
            <input class="inp" type="file" name="file[{{ str_replace('.', '__', $s->key) }}]" accept="image/*" style="margin-top:6px">
          </div>

        {{-- ── BOOLEAN ── --}}
        @elseif($s->type === 'boolean')
          <div class="fld">
            <label style="display:flex;align-items:center;gap:9px;cursor:pointer">
              <input type="hidden" name="val[{{ $s->key }}]" value="0">
              <input type="checkbox" name="val[{{ $s->key }}]" value="1" @checked($val)> {{ $s->label }}
            </label>
          </div>

        {{-- ── TEXTAREA ── --}}
        @elseif($s->type === 'textarea')
          <div class="fld"><label>{{ $s->label }}</label><textarea class="inp" rows="3" name="val[{{ $s->key }}]">{{ $val }}</textarea></div>

        {{-- ── TEXT / NUMBER ── --}}
        @else
          <div class="fld"><label>{{ $s->label }}</label><input class="inp" type="text" name="val[{{ $s->key }}]" value="{{ $val }}"></div>
        @endif

      @empty
        <p style="color:var(--muted)">Belum ada konten pada tab ini.</p>
      @endforelse

    </div>
  </div>
</form>

@push('styles')
<style>
.content-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px}
.ctab{padding:8px 15px;border-radius:9px;font-size:13px;font-weight:600;color:var(--muted);background:#fff;border:1px solid var(--border);transition:all .15s}
.ctab:hover{border-color:var(--blue);color:var(--blue)}
.ctab.on{background:var(--blue);color:#fff;border-color:var(--blue)}
.repeater{display:flex;flex-direction:column;gap:12px;margin:10px 0}
.rep-item{border:1px solid var(--border);border-radius:12px;padding:14px;background:#fafbfe}
.rep-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-weight:700;font-size:12.5px;color:var(--muted)}
.rep-del{background:#fee2e2;color:var(--red);border:none;border-radius:7px;width:28px;height:28px;cursor:pointer}
.rep-del:hover{background:#fecaca}
.rep-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
.rep-grid .fld:has(textarea){grid-column:1/-1}
.fld{margin-bottom:14px}
.fld>label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
@media(max-width:640px){.rep-grid{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
function repDel(btn){
  var item = btn.closest('.rep-item');
  var rep = item.closest('.repeater');
  if(rep.querySelectorAll('.rep-item').length <= 1){ item.querySelectorAll('input,textarea').forEach(e=>e.value=''); return; }
  item.remove(); reindex(rep);
}
function repAdd(key){
  var rep = document.querySelector('.repeater[data-key="'+key+'"]');
  var tpl = document.getElementById('tpl-'+key);
  var node = tpl.content.cloneNode(true);
  rep.appendChild(node);
  reindex(rep);
}
function reindex(rep){
  rep.querySelectorAll('.rep-item').forEach(function(item, idx){
    item.querySelector('.rep-head span').textContent = '#'+(idx+1);
    item.querySelectorAll('[data-name],[name]').forEach(function(el){
      var tmpl = el.getAttribute('data-name') || el.getAttribute('name');
      if(!tmpl) return;
      var name = tmpl.replace(/\[(\d+|__IDX__)\]/, '['+idx+']');
      el.setAttribute('name', name);
      el.removeAttribute('data-name');
    });
  });
}
</script>
@endpush
@endsection
