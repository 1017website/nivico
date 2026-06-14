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

  <div class="table-wrap"><table>
    <thead><tr><th></th><th>Produk</th><th>Harga</th><th>Stok</th><th>Flash Sale</th></tr></thead>
    <tbody>
      @forelse($products as $p)
        <tr>
          <td style="width:56px">
            <img class="thumb" src="{{ $p->image ?: asset('images/placeholder-product.svg') }}" alt="" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">
          </td>
          <td style="font-weight:600">{{ $p->name }}<div style="font-size:11px;color:var(--muted);font-weight:400">{{ $p->sku }}</div></td>
          <td>Rp{{ number_format($p->price,0,',','.') }}</td>
          <td><span class="chip {{ $p->stock < 10 ? 'low' : 'ok' }}">{{ $p->stock }}</span></td>
          <td>
            <form method="POST" action="{{ route('admin.flashsale.toggle', $p) }}" style="display:inline">
              @csrf @method('PATCH')
              <button type="submit" class="toggle-sw {{ $p->is_flash_sale ? 'on' : '' }}" title="Klik untuk ubah">
                <span class="toggle-knob"></span>
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty"><div class="ei">⚡</div>Belum ada produk. Tambahkan dengan menyalakan toggle.</td></tr>
      @endforelse
    </tbody>
  </table></div>
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
@endsection
