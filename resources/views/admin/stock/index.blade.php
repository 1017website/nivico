@extends('layouts.admin')
@section('title', 'Stok')
@section('heading', 'Manajemen Stok')

@section('content')

<div class="cards">
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-cubes"></i></span> Total SKU</div><div class="val">{{ number_format($summary['total_sku']) }}</div></div>
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-warehouse"></i></span> Total Unit</div><div class="val">{{ number_format($summary['total_unit']) }}</div></div>
  <div class="card"><div class="lbl"><span class="ci" style="background:#fef3c7;color:#92400e"><i class="fa-solid fa-triangle-exclamation"></i></span> Stok Menipis</div><div class="val">{{ $summary['low'] }}</div><div class="sub">≤ 5 unit</div></div>
  <div class="card"><div class="lbl"><span class="ci" style="background:#fee2e2;color:#991b1b"><i class="fa-solid fa-ban"></i></span> Stok Habis</div><div class="val">{{ $summary['out'] }}</div></div>
</div>

<div class="toolbar">
  <form method="GET">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU...">
    <select class="inp" name="kategori" onchange="this.form.submit()" style="max-width:180px">
      <option value="">Semua Kategori</option>
      @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('kategori')==$c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <button class="btn btn-gray btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <a href="{{ route('admin.stock.index', ['filter'=>'low']) }}" class="btn btn-sm {{ request('filter')=='low'?'btn-blue':'btn-gray' }}">Menipis</a>
  <a href="{{ route('admin.stock.index', ['filter'=>'out']) }}" class="btn btn-sm {{ request('filter')=='out'?'btn-blue':'btn-gray' }}">Habis</a>
  <div style="margin-left:auto;display:flex;gap:8px">
    <a href="{{ route('admin.stock.opname') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-clipboard-check"></i> Stock Opname</a>
    <a href="{{ route('admin.stock.movements') }}" class="btn btn-gray btn-sm"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</a>
  </div>
</div>

<div class="panel">
  <div class="table-wrap"><table>
    <thead><tr><th>Produk</th><th>SKU</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($products as $p)
      <tr>
        <td style="display:flex;align-items:center;gap:11px;font-weight:600">@if($p->image)<img class="thumb" src="{{ $p->image }}" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">@endif {{ $p->name }}</td>
        <td style="color:var(--muted)">{{ $p->sku }}</td>
        <td>{{ $p->category->name ?? '—' }}</td>
        <td style="font-weight:800;font-size:15px">{{ $p->stock }}</td>
        <td>
          @if($p->stock <= 0)<span class="chip out">Habis</span>
          @elseif($p->stock <= 5)<span class="chip low">Menipis</span>
          @else<span class="chip ok">Aman</span>@endif
        </td>
        <td><button class="btn btn-blue btn-sm js-adjust" data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $p->stock }}"><i class="fa-solid fa-sliders"></i> Sesuaikan</button></td>
      </tr>
      @empty
      <tr><td colspan="6"><div class="empty"><div class="ei"><i class="fa-solid fa-box-open"></i></div>Tidak ada produk.</div></td></tr>
      @endforelse
    </tbody>
  </table></div>
  <div class="pag">{{ $products->links() }}</div>
</div>

{{-- Modal Adjust --}}
<div id="adjustModal" style="display:none;position:fixed;inset:0;background:rgba(15,29,107,.45);z-index:200;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:16px;max-width:440px;width:100%;overflow:hidden;box-shadow:var(--shadow-lg)">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h2 style="font-size:16px;font-weight:800">Sesuaikan Stok</h2>
      <button onclick="closeAdjust()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted)">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.stock.adjust') }}" style="padding:24px">@csrf
      <input type="hidden" name="product_id" id="adj_pid">
      <div style="font-size:14px;font-weight:600;margin-bottom:4px" id="adj_name"></div>
      <div style="font-size:12.5px;color:var(--muted);margin-bottom:18px">Stok saat ini: <strong id="adj_cur"></strong> unit</div>
      <div class="fld">
        <label>Jenis Penyesuaian</label>
        <select class="inp" name="mode" id="adj_mode">
          <option value="in">Tambah Stok (+)</option>
          <option value="out">Kurangi Stok (−)</option>
          <option value="set">Set Nilai Absolut</option>
        </select>
      </div>
      <div class="fld">
        <label>Jumlah</label>
        <input class="inp" type="number" name="qty" min="0" value="0" required>
      </div>
      <div class="fld">
        <label>Alasan (opsional)</label>
        <input class="inp" type="text" name="reason" placeholder="mis. barang rusak, retur, koreksi...">
      </div>
      <button class="btn btn-blue" type="submit" style="width:100%;justify-content:center"><i class="fa-solid fa-check"></i> Simpan Penyesuaian</button>
    </form>
  </div>
</div>

@push('scripts')
<script>
function openAdjust(id,name,cur){
  document.getElementById('adj_pid').value=id;
  document.getElementById('adj_name').textContent=name;
  document.getElementById('adj_cur').textContent=cur;
  document.getElementById('adjustModal').style.display='flex';
}
function closeAdjust(){document.getElementById('adjustModal').style.display='none';}
document.querySelectorAll('.js-adjust').forEach(function(btn){
  btn.addEventListener('click',function(){
    openAdjust(this.dataset.id, this.dataset.name, this.dataset.stock);
  });
});
document.getElementById('adjustModal').addEventListener('click',function(e){if(e.target===this)closeAdjust();});
</script>
@endpush
@endsection
