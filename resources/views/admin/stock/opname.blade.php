@extends('layouts.admin')
@section('title', 'Stock Opname')
@section('heading', 'Stock Opname')

@section('content')

<div style="margin-bottom:18px"><a class="btn btn-sm btn-gray" href="{{ route('admin.stock.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a></div>

<div class="panel">
  <div class="panel-hd">
    <div>
      <h2>Penghitungan Fisik Stok</h2>
      <div class="sub">Isi jumlah fisik hasil hitung. Baris yang dibiarkan kosong tidak diubah. Selisih otomatis tercatat di riwayat.</div>
    </div>
  </div>

  <form method="POST" action="{{ route('admin.stock.opname.store') }}">@csrf
    <div style="padding:16px 22px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:end">
      <div class="fld" style="margin:0;flex:1;max-width:260px">
        <label>No. Referensi Opname</label>
        <input class="inp" type="text" name="reference" placeholder="OPN-{{ now()->format('Ymd') }}-001">
      </div>
      <form method="GET" style="display:flex;gap:8px;flex:1;max-width:360px;margin:0">
        <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk...">
        <button class="btn btn-gray btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
    </div>

    <div class="table-wrap"><table>
      <thead><tr><th>Produk</th><th>SKU</th><th>Stok Sistem</th><th style="width:160px">Hitung Fisik</th><th>Selisih</th></tr></thead>
      <tbody>
        @forelse($products as $p)
        <tr data-sys="{{ $p->stock }}">
          <td style="font-weight:600">{{ $p->name }}</td>
          <td style="color:var(--muted)">{{ $p->sku }}</td>
          <td style="font-weight:700">{{ $p->stock }}</td>
          <td><input class="inp opname-inp" type="number" min="0" name="counts[{{ $p->id }}]" placeholder="{{ $p->stock }}" oninput="calcDiff(this)"></td>
          <td class="diff" style="font-weight:700;color:var(--muted)">—</td>
        </tr>
        @empty
        <tr><td colspan="5"><div class="empty">Tidak ada produk.</div></td></tr>
        @endforelse
      </tbody>
    </table></div>
    <div style="padding:18px 22px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
      <div style="font-size:12.5px;color:var(--muted)">{{ $products->total() }} produk · halaman {{ $products->currentPage() }}/{{ $products->lastPage() }}</div>
      <button class="btn btn-green" type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Hasil Opname</button>
    </div>
  </form>
</div>

<div class="pag">{{ $products->links() }}</div>

@push('scripts')
<script>
function calcDiff(inp){
  var tr=inp.closest('tr'),sys=parseInt(tr.dataset.sys||'0',10),cell=tr.querySelector('.diff');
  if(inp.value===''){cell.textContent='—';cell.style.color='var(--muted)';return;}
  var d=parseInt(inp.value,10)-sys;
  cell.textContent=(d>0?'+':'')+d;
  cell.style.color=d===0?'var(--muted)':(d>0?'var(--green)':'var(--red)');
}
</script>
@endpush
@endsection
