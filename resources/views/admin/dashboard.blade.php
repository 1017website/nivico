@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="cards">
  <div class="card"><div class="lbl">Total Pesanan</div><div class="val">{{ number_format($stats['orders']) }}</div><div class="sub" style="color:var(--muted)">{{ $stats['pending'] }} menunggu pembayaran</div></div>
  <div class="card"><div class="lbl">Pendapatan</div><div class="val" style="font-size:20px">Rp{{ number_format($stats['revenue'], 0, ',', '.') }}</div><div class="sub" style="color:var(--green)">dari pesanan dibayar</div></div>
  <div class="card"><div class="lbl">Produk</div><div class="val">{{ number_format($stats['products']) }}</div><div class="sub" style="color:{{ $stats['low_stock'] ? 'var(--red)' : 'var(--muted)' }}">{{ $stats['low_stock'] }} stok menipis</div></div>
  <div class="card"><div class="lbl">Pelanggan</div><div class="val">{{ number_format($stats['customers']) }}</div><div class="sub" style="color:{{ $stats['unread_msg'] ? 'var(--blue)' : 'var(--muted)' }}">{{ $stats['unread_msg'] }} pesan belum dibaca</div></div>
</div>

<div class="panel">
  <div class="panel-hd"><h2>Pesanan Terbaru</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.orders.index') }}">Lihat Semua</a></div>
  @if($recentOrders->isEmpty())
    <div class="empty">Belum ada pesanan.</div>
  @else
  <table>
    <thead><tr><th>No. Pesanan</th><th>Penerima</th><th>Total</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
    <tbody>
      @foreach($recentOrders as $o)
        <tr>
          <td style="font-weight:700">#{{ $o->order_number }}</td>
          <td>{{ $o->recipient_name }}</td>
          <td>Rp{{ number_format($o->total, 0, ',', '.') }}</td>
          <td><span class="badge b-{{ $o->status }}">{{ $o->statusLabel() }}</span></td>
          <td style="color:var(--muted)">{{ $o->created_at->format('d M Y') }}</td>
          <td><a class="btn btn-sm btn-gray" href="{{ route('admin.orders.show', $o) }}">Detail</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>

<div class="panel">
  <div class="panel-hd"><h2>Stok Menipis (&lt; 10)</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.products.index') }}">Kelola Produk</a></div>
  @if($lowStock->isEmpty())
    <div class="empty">Semua stok aman 👍</div>
  @else
  <table>
    <thead><tr><th>Produk</th><th>SKU</th><th>Sisa Stok</th><th></th></tr></thead>
    <tbody>
      @foreach($lowStock as $p)
        <tr>
          <td style="display:flex;align-items:center;gap:10px"><img class="thumb" src="{{ $p->image }}" alt="">{{ $p->name }}</td>
          <td style="color:var(--muted)">{{ $p->sku }}</td>
          <td><span class="badge b-cancelled">{{ $p->stock }}</span></td>
          <td><a class="btn btn-sm btn-blue" href="{{ route('admin.products.edit', $p) }}">Edit</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @endif
</div>
@endsection
