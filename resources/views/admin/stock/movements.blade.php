@extends('layouts.admin')
@section('title', 'Riwayat Stok')
@section('heading', 'Riwayat Pergerakan Stok')

@section('content')

<div style="margin-bottom:18px"><a class="btn btn-sm btn-gray" href="{{ route('admin.stock.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali</a></div>

<div class="toolbar">
  <form method="GET">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk / SKU...">
    <select class="inp" name="type" onchange="this.form.submit()" style="max-width:180px">
      <option value="">Semua Jenis</option>
      @foreach(['adjustment'=>'Penyesuaian','opname'=>'Stock Opname','sale'=>'Penjualan','restock'=>'Pengembalian','initial'=>'Stok Awal'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('type')==$k)>{{ $v }}</option>
      @endforeach
    </select>
    <button class="btn btn-gray btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
</div>

<div class="panel">
  <div class="table-wrap"><table>
    <thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Perubahan</th><th>Sebelum → Sesudah</th><th>Referensi</th><th>Oleh</th></tr></thead>
    <tbody>
      @forelse($movements as $m)
      <tr>
        <td style="color:var(--muted);white-space:nowrap">{{ $m->created_at->format('d M Y H:i') }}</td>
        <td style="font-weight:600">{{ $m->product->name ?? '—' }}<div style="font-size:11px;color:var(--muted)">{{ $m->reason }}</div></td>
        <td>
          @php $tc=['adjustment'=>'b-processing','opname'=>'b-paid','sale'=>'b-cancelled','restock'=>'b-completed','initial'=>'b-shipped'][$m->type]??'b-pending'; @endphp
          <span class="badge {{ $tc }}">{{ $m->typeLabel() }}</span>
        </td>
        <td style="font-weight:800;color:{{ $m->qty_change>0?'var(--green)':($m->qty_change<0?'var(--red)':'var(--muted)') }}">{{ $m->qty_change>0?'+':'' }}{{ $m->qty_change }}</td>
        <td style="color:var(--muted)">{{ $m->stock_before }} → <strong style="color:var(--ink)">{{ $m->stock_after }}</strong></td>
        <td style="color:var(--muted)">{{ $m->reference ?? '—' }}</td>
        <td style="color:var(--muted)">{{ $m->user_name ?? 'Sistem' }}</td>
      </tr>
      @empty
      <tr><td colspan="7"><div class="empty"><div class="ei"><i class="fa-solid fa-clock-rotate-left"></i></div>Belum ada pergerakan stok.</div></td></tr>
      @endforelse
    </tbody>
  </table></div>
  <div class="pag">{{ $movements->links() }}</div>
</div>

@endsection
