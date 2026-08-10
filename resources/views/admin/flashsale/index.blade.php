@extends('layouts.admin')
@section('title', 'Flash Sale')
@section('heading', 'Flash Sale')

@section('content')

{{-- PENGATURAN COUNTDOWN --}}
<div class="panel">
  <div class="panel-hd"><h2>Pengaturan Flash Sale</h2><span class="chip ok">{{ $flashCount }} produk aktif</span></div>
  <form method="POST" action="{{ route('admin.flashsale.settings') }}" style="padding:18px 22px">
    @csrf
    <div class="frm-grid">
      <div class="fld">
        <label>Judul Section</label>
        <input class="inp" type="text" name="title" value="{{ $settings['title'] }}" placeholder="⚡ Flash Sale">
      </div>
      <div class="fld">
        <label>Label Countdown</label>
        <input class="inp" type="text" name="label" value="{{ $settings['label'] }}" placeholder="Berakhir dalam:">
      </div>
      <div class="fld">
        <label>Waktu Berakhir</label>
        <input class="inp" type="datetime-local" name="ends_at"
               value="{{ $settings['ends_at'] ? \Illuminate\Support\Carbon::parse($settings['ends_at'])->format('Y-m-d\TH:i') : '' }}">
        <small style="color:var(--muted);font-size:11.5px;margin-top:4px">Countdown di beranda menghitung mundur ke waktu ini.</small>
      </div>
      <div class="fld">
        <label>Status</label>
        <label style="display:flex;align-items:center;gap:9px;cursor:pointer;margin-top:6px">
          <input type="hidden" name="enabled" value="0">
          <input type="checkbox" name="enabled" value="1" @checked($settings['enabled'])> Tampilkan countdown di beranda
        </label>
      </div>
    </div>
    <button class="btn btn-blue" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan</button>
  </form>
</div>

{{-- DAFTAR PRODUK --}}
<div class="panel">
  <div class="panel-hd">
    <h2>Produk dalam Flash Sale</h2>
    @if($flashCount > 0)
    <form method="POST" action="{{ route('admin.flashsale.clear') }}" onsubmit="return confirm('Keluarkan semua produk dari Flash Sale?')">
      @csrf
      <button class="btn btn-gray btn-sm" type="submit"><i class="fa-solid fa-xmark"></i> Kosongkan Semua</button>
    </form>
    @endif
  </div>

  <div class="toolbar" style="padding:14px 22px 0;margin-bottom:0">
    <form method="GET" action="{{ route('admin.flashsale.index') }}">
      <input class="inp" type="text" name="q" value="{{ $q }}" placeholder="Cari produk...">
      <button class="btn btn-blue btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
  </div>

  <form method="POST" action="{{ route('admin.flashsale.bulk') }}" id="flash-bulk-form">
    @csrf @method('PATCH')
    <div style="padding:12px 22px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;border-top:1px solid var(--border)">
      <span id="flash-selected-count" style="font-size:12.5px;color:var(--muted);margin-right:auto">0 produk dipilih</span>
      <button class="btn btn-blue btn-sm" type="submit" name="action" value="add"><i class="fa-solid fa-bolt"></i> Masukkan Terpilih</button>
      <button class="btn btn-gray btn-sm" type="submit" name="action" value="remove">Keluarkan Terpilih</button>
    </div>
  <div class="table-wrap"><table>
    <thead><tr><th style="width:42px"><input type="checkbox" id="flash-select-all" aria-label="Pilih semua produk di halaman ini"></th><th></th><th>Produk</th><th>Harga</th><th>Stok</th><th>Flash Sale</th></tr></thead>
    <tbody>
      @forelse($products as $p)
        <tr>
          <td><input class="flash-product-check" type="checkbox" name="product_ids[]" value="{{ $p->id }}" aria-label="Pilih {{ $p->name }}"></td>
          <td style="width:56px">
            <img class="thumb" src="{{ $p->image ?: asset('images/placeholder-product.svg') }}" alt="" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">
          </td>
          <td style="font-weight:600">{{ $p->name }}<div style="font-size:11px;color:var(--muted);font-weight:400">{{ $p->sku }}</div></td>
          <td>Rp{{ number_format($p->price,0,',','.') }}</td>
          <td><span class="chip {{ $p->stock < 10 ? 'low' : 'ok' }}">{{ $p->stock }}</span></td>
          <td>
            <button form="flash-toggle-{{ $p->id }}" type="submit" class="toggle-sw {{ $p->is_flash_sale ? 'on' : '' }}" title="Klik untuk ubah">
              <span class="toggle-knob"></span>
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="empty"><div class="ei">⚡</div>Belum ada produk. Tambahkan dengan menyalakan toggle.</td></tr>
      @endforelse
    </tbody>
  </table></div>
  </form>
  @foreach($products as $p)
    <form id="flash-toggle-{{ $p->id }}" method="POST" action="{{ route('admin.flashsale.toggle', $p) }}" style="display:none">@csrf @method('PATCH')</form>
  @endforeach
  @if($products->hasPages())<div class="pag">{{ $products->links() }}</div>@endif
</div>

@push('styles')
<style>
.toggle-sw{width:46px;height:26px;border-radius:99px;background:#cbd5e1;border:none;cursor:pointer;position:relative;transition:background .2s;padding:0}
.toggle-sw.on{background:var(--green)}
.toggle-knob{position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)}
.toggle-sw.on .toggle-knob{transform:translateX(20px)}
</style>
@endpush
@push('scripts')
<script>
(function(){
  const all = document.getElementById('flash-select-all');
  const checks = Array.from(document.querySelectorAll('.flash-product-check'));
  const count = document.getElementById('flash-selected-count');
  const form = document.getElementById('flash-bulk-form');
  if (!all || !form) return;
  function sync(){
    const selected = checks.filter(c => c.checked).length;
    count.textContent = selected + ' produk dipilih';
    all.checked = checks.length > 0 && selected === checks.length;
    all.indeterminate = selected > 0 && selected < checks.length;
  }
  all.addEventListener('change', () => { checks.forEach(c => c.checked = all.checked); sync(); });
  checks.forEach(c => c.addEventListener('change', sync));
  form.addEventListener('submit', function(e){
    if (!checks.some(c => c.checked)) { e.preventDefault(); alert('Pilih minimal satu produk.'); }
  });
  sync();
})();
</script>
@endpush
@endsection
