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
                'hero.slides'   => ['title1'=>'Judul Baris 1','title2'=>'Judul Baris 2','desc'=>'Deskripsi','image'=>'Gambar','cta_text'=>'Teks Tombol','cta_link'=>'Link Tombol'],
                'hero.perks'    => ['t1'=>'Baris 1','t2'=>'Baris 2'],
                'banner.promos' => ['tag'=>'Tag','title'=>'Judul (boleh <br>)','btn'=>'Teks Tombol','link'=>'Link','image'=>'Gambar'],
                'about.stats'   => ['value'=>'Angka','label'=>'Keterangan'],
                'about.missions'=> ['text'=>'Isi Misi'],
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
                      <div class="fld {{ $fk === 'image' ? 'image-field' : '' }}">
                        <label>{{ $flabel }}</label>
                        @if($fk === 'image')
                          <div class="image-preview" @if(empty($row[$fk])) hidden @endif>
                            <img src="{{ $row[$fk] ?? '' }}" alt="Preview gambar">
                          </div>
                          <div class="image-url-wrap">
                            <i class="fa-solid fa-link" aria-hidden="true"></i>
                            <input class="inp image-url" type="text" name="json[{{ $s->key }}][{{ $i }}][{{ $fk }}]" value="{{ $row[$fk] ?? '' }}" placeholder="Tempel URL gambar" onchange="previewImageUrl(this)">
                          </div>
                          <div class="image-or"><span>atau</span></div>
                          <div class="image-upload">
                            <label class="image-dropzone" ondragover="imageDragOver(event)" ondragleave="imageDragLeave(event)" ondrop="imageDrop(event)">
                              <input class="image-file" type="file" name="json_file[{{ str_replace('.', '__', $s->key) }}][{{ $i }}][image]" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" onchange="previewImageFile(this)" hidden>
                              <span class="image-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                              <span class="image-upload-copy">
                                <strong class="image-upload-title">Tarik & lepas gambar di sini</strong>
                                <small class="image-upload-subtitle">JPG, PNG, WebP, atau GIF · maksimal 5 MB</small>
                              </span>
                              <span class="image-upload-action"><i class="fa-regular fa-folder-open"></i> Pilih file</span>
                            </label>
                            <div class="image-file-info" hidden>
                              <span class="image-file-icon"><i class="fa-regular fa-image"></i></span>
                              <span class="image-file-detail"><strong></strong><small></small></span>
                              <button type="button" class="image-file-remove" onclick="clearImageFile(this)" aria-label="Batalkan file yang dipilih"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                          </div>
                        @elseif(in_array($fk, ['desc', 'title', 'text']))
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
                    <div class="fld {{ $fk === 'image' ? 'image-field' : '' }}">
                      <label>{{ $flabel }}</label>
                      @if($fk === 'image')
                        <div class="image-preview" hidden><img src="" alt="Preview gambar"></div>
                        <div class="image-url-wrap">
                          <i class="fa-solid fa-link" aria-hidden="true"></i>
                          <input class="inp image-url" type="text" data-name="json[{{ $s->key }}][__IDX__][{{ $fk }}]" placeholder="Tempel URL gambar" onchange="previewImageUrl(this)">
                        </div>
                        <div class="image-or"><span>atau</span></div>
                        <div class="image-upload">
                          <label class="image-dropzone" ondragover="imageDragOver(event)" ondragleave="imageDragLeave(event)" ondrop="imageDrop(event)">
                            <input class="image-file" type="file" data-name="json_file[{{ str_replace('.', '__', $s->key) }}][__IDX__][image]" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" onchange="previewImageFile(this)" hidden>
                            <span class="image-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                            <span class="image-upload-copy">
                              <strong class="image-upload-title">Tarik & lepas gambar di sini</strong>
                              <small class="image-upload-subtitle">JPG, PNG, WebP, atau GIF · maksimal 5 MB</small>
                            </span>
                            <span class="image-upload-action"><i class="fa-regular fa-folder-open"></i> Pilih file</span>
                          </label>
                          <div class="image-file-info" hidden>
                            <span class="image-file-icon"><i class="fa-regular fa-image"></i></span>
                            <span class="image-file-detail"><strong></strong><small></small></span>
                            <button type="button" class="image-file-remove" onclick="clearImageFile(this)" aria-label="Batalkan file yang dipilih"><i class="fa-solid fa-xmark"></i></button>
                          </div>
                        </div>
                      @elseif(in_array($fk, ['desc', 'title', 'text']))
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
          <div class="fld image-field">
            <label>{{ $s->label }}</label>
            <div class="image-preview image-preview-compact" @if(!$val) hidden @endif><img src="{{ $val }}" alt="Preview gambar"></div>
            <div class="image-url-wrap">
              <i class="fa-solid fa-link" aria-hidden="true"></i>
              <input class="inp image-url" type="text" name="val[{{ $s->key }}]" value="{{ $val }}" placeholder="Tempel URL gambar" onchange="previewImageUrl(this)">
            </div>
            <div class="image-or"><span>atau</span></div>
            <div class="image-upload">
              <label class="image-dropzone" ondragover="imageDragOver(event)" ondragleave="imageDragLeave(event)" ondrop="imageDrop(event)">
                <input class="image-file" type="file" name="file[{{ str_replace('.', '__', $s->key) }}]" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif" onchange="previewImageFile(this)" hidden>
                <span class="image-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                <span class="image-upload-copy">
                  <strong class="image-upload-title">Tarik & lepas gambar di sini</strong>
                  <small class="image-upload-subtitle">JPG, PNG, WebP, atau GIF · maksimal 5 MB</small>
                </span>
                <span class="image-upload-action"><i class="fa-regular fa-folder-open"></i> Pilih file</span>
              </label>
              <div class="image-file-info" hidden>
                <span class="image-file-icon"><i class="fa-regular fa-image"></i></span>
                <span class="image-file-detail"><strong></strong><small></small></span>
                <button type="button" class="image-file-remove" onclick="clearImageFile(this)" aria-label="Batalkan file yang dipilih"><i class="fa-solid fa-xmark"></i></button>
              </div>
            </div>
          </div>

        {{-- ── BOOLEAN ── --}}
        @elseif($s->type === 'boolean')
          <div class="fld">
            <label style="display:flex;align-items:center;gap:9px;cursor:pointer">
              <input type="hidden" name="val[{{ $s->key }}]" value="0">
              <input type="checkbox" name="val[{{ $s->key }}]" value="1" @checked($val)> {{ $s->label }}
            </label>
          </div>

        @elseif($s->key === 'checkout.service_fee_type')
          <div class="fld">
            <label>{{ $s->label }}</label>
            <select class="inp" name="val[{{ $s->key }}]">
              <option value="fixed" @selected($val === 'fixed')>Rupiah (Rp)</option>
              <option value="percent" @selected($val === 'percent')>Persentase (%)</option>
            </select>
            <small style="color:var(--muted)">Persentase dihitung dari total setelah diskon dan ongkir.</small>
          </div>

        {{-- ── TEXTAREA ── --}}
        @elseif($s->type === 'textarea')
          <div class="fld"><label>{{ $s->label }}</label><textarea class="inp" rows="3" name="val[{{ $s->key }}]">{{ $val }}</textarea></div>

        {{-- ── TEXT / NUMBER ── --}}
        @else
          <div class="fld"><label>{{ $s->label }}</label><input class="inp" type="{{ $s->type === 'number' ? 'number' : 'text' }}" name="val[{{ $s->key }}]" value="{{ $val }}" @if($s->type === 'number') min="0" step="0.01" @endif></div>
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
.rep-grid .image-field{grid-column:1/-1}
.fld{margin-bottom:14px}
.fld>label{display:block;font-size:12.5px;font-weight:600;margin-bottom:5px}
.image-preview{width:100%;height:150px;margin-bottom:8px;border:1px solid var(--border);border-radius:9px;overflow:hidden;background:#eef2f7}
.image-preview-compact{height:110px}
.image-preview img{width:100%;height:100%;display:block;object-fit:cover}
.image-url-wrap{position:relative}
.image-url-wrap>i{position:absolute;left:13px;top:50%;z-index:1;transform:translateY(-50%);color:#94a3b8;font-size:12px;pointer-events:none}
.image-url-wrap .inp{padding-left:36px}
.image-or{display:flex;align-items:center;gap:10px;margin:7px 0;color:var(--muted);font-size:11px;text-transform:uppercase}
.image-or::before,.image-or::after{content:"";height:1px;flex:1;background:var(--border)}
.image-dropzone{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:13px;min-height:92px;padding:15px 17px;border:1.5px dashed #b8c5d8;border-radius:11px;background:#fff;cursor:pointer;transition:border-color .18s,background .18s,box-shadow .18s}
.image-dropzone:hover,.image-dropzone.is-dragging{border-color:var(--blue);background:#f5f8ff;box-shadow:0 0 0 3px rgba(37,99,235,.08)}
.image-dropzone.has-file{border-style:solid;border-color:#86b49b;background:#f6fbf8}
.image-upload-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:10px;background:#eaf0ff;color:var(--blue);font-size:18px}
.image-dropzone.has-file .image-upload-icon{background:#dcf4e5;color:#16834c}
.image-upload-copy{display:flex;flex-direction:column;min-width:0;gap:4px}
.image-upload-title{font-size:13px;color:#263348;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.image-upload-subtitle{font-size:11.5px;color:var(--muted)}
.image-upload-action{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#34435a;font-size:12px;font-weight:700;white-space:nowrap;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.image-dropzone:hover .image-upload-action{border-color:#9eb5df;color:var(--blue)}
.image-file-info{display:flex;align-items:center;gap:10px;margin-top:8px;padding:9px 11px;border:1px solid #dce6e0;border-radius:9px;background:#f8fcf9}
.image-file-info[hidden]{display:none}
.image-file-icon{display:grid;place-items:center;width:30px;height:30px;border-radius:7px;background:#dcf4e5;color:#16834c}
.image-file-detail{display:flex;flex:1;min-width:0;flex-direction:column;gap:2px}
.image-file-detail strong{font-size:12px;color:#263348;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.image-file-detail small{font-size:11px;color:var(--muted)}
.image-file-remove{display:grid;place-items:center;width:28px;height:28px;border:0;border-radius:7px;background:transparent;color:#94a3b8;cursor:pointer}
.image-file-remove:hover{background:#fee2e2;color:var(--red)}
@media(max-width:640px){.rep-grid{grid-template-columns:1fr}.image-dropzone{grid-template-columns:auto 1fr}.image-upload-action{grid-column:1/-1;justify-content:center}.image-preview{height:120px}}
</style>
@endpush

@push('scripts')
<script>
function repDel(btn){
  var item = btn.closest('.rep-item');
  var rep = item.closest('.repeater');
  if(rep.querySelectorAll('.rep-item').length <= 1){
    item.querySelectorAll('input,textarea').forEach(e=>e.value='');
    item.querySelectorAll('.image-field').forEach(resetImageField);
    return;
  }
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
function setImagePreview(field, src){
  var box = field.querySelector('.image-preview');
  var img = box.querySelector('img');
  if(src){ img.src = src; box.hidden = false; }
  else { img.removeAttribute('src'); box.hidden = true; }
}
function previewImageFile(input){
  var field = input.closest('.image-field');
  var file = input.files && input.files[0];
  if(!file){ updateImageUploadUi(input, null); previewImageUrl(field.querySelector('.image-url')); return; }
  var allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if(!allowedTypes.includes(file.type)){
    input.value = '';
    updateImageUploadUi(input, null);
    toast('✗ Format gambar harus JPG, PNG, WebP, atau GIF.');
    return;
  }
  if(file.size > 5 * 1024 * 1024){
    input.value = '';
    updateImageUploadUi(input, null);
    toast('✗ Ukuran gambar maksimal 5 MB.');
    return;
  }
  if(input._previewUrl) URL.revokeObjectURL(input._previewUrl);
  input._previewUrl = URL.createObjectURL(file);
  setImagePreview(field, input._previewUrl);
  updateImageUploadUi(input, file);
}
function previewImageUrl(input){
  var field = input.closest('.image-field');
  var fileInput = field.querySelector('.image-file');
  if(fileInput.files && fileInput.files.length) return;
  setImagePreview(field, input.value.trim());
}
function updateImageUploadUi(input, file){
  var upload = input.closest('.image-upload');
  var zone = upload.querySelector('.image-dropzone');
  var info = upload.querySelector('.image-file-info');
  zone.classList.toggle('has-file', !!file);
  zone.querySelector('.image-upload-title').textContent = file ? 'Gambar siap diunggah' : 'Tarik & lepas gambar di sini';
  if(!file){ info.hidden = true; return; }
  info.querySelector('strong').textContent = file.name;
  info.querySelector('small').textContent = formatFileSize(file.size);
  info.hidden = false;
}
function formatFileSize(bytes){
  if(bytes < 1024) return bytes + ' B';
  if(bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}
function clearImageFile(button){
  var field = button.closest('.image-field');
  var input = field.querySelector('.image-file');
  if(input._previewUrl) URL.revokeObjectURL(input._previewUrl);
  input.value = '';
  updateImageUploadUi(input, null);
  previewImageUrl(field.querySelector('.image-url'));
}
function resetImageField(field){
  var input = field.querySelector('.image-file');
  if(input) updateImageUploadUi(input, null);
  setImagePreview(field, '');
}
function imageDragOver(event){
  event.preventDefault();
  event.currentTarget.classList.add('is-dragging');
}
function imageDragLeave(event){
  event.currentTarget.classList.remove('is-dragging');
}
function imageDrop(event){
  event.preventDefault();
  var zone = event.currentTarget;
  zone.classList.remove('is-dragging');
  var file = event.dataTransfer.files && event.dataTransfer.files[0];
  if(!file) return;
  var transfer = new DataTransfer();
  transfer.items.add(file);
  var input = zone.querySelector('.image-file');
  input.files = transfer.files;
  previewImageFile(input);
}
</script>
@endpush
@endsection
