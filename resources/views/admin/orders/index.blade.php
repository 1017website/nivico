@extends('layouts.admin')
@section('title', 'Pesanan')
@section('heading', 'Pesanan')

@section('content')
@php $statuses = ['pending'=>'Menunggu','paid'=>'Dibayar','processing'=>'Diproses','shipped'=>'Dikirim','completed'=>'Selesai','cancelled'=>'Dibatalkan']; @endphp
<div class="toolbar">
  <form method="GET" action="{{ route('admin.orders.index') }}">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. pesanan / penerima...">
    <select class="inp" name="status" onchange="this.form.submit()" style="max-width:160px">
      <option value="">Semua Status</option>
      @foreach($statuses as $key => $lbl)
        <option value="{{ $key }}" @selected(request('status')===$key)>{{ $lbl }}</option>
      @endforeach
    </select>
    <button class="btn btn-gray" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
</div>

<div class="panel">
  @if($orders->isEmpty())
    <div class="empty">Belum ada pesanan.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>No. Pesanan</th><th>Penerima</th><th>Items</th><th>Total</th><th>Bayar</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
    <tbody>
      @foreach($orders as $o)
        <tr>
          <td style="font-weight:700">#{{ $o->order_number }}</td>
          <td>{{ $o->recipient_name }}<div style="font-size:11.5px;color:var(--muted)">{{ $o->phone }}</div></td>
          <td>{{ $o->items->count() }} item</td>
          <td style="font-weight:600">Rp{{ number_format($o->total, 0, ',', '.') }}</td>
          <td style="text-transform:capitalize;color:var(--muted)">{{ str_replace('_',' ',$o->payment_gateway) }}</td>
          <td><span class="badge ps-{{ $o->payment_status }}">{{ $o->paymentStatusLabel() }}</span></td>
          <td><span class="badge b-{{ $o->status }}">{{ $o->statusLabel() }}</span></td>
          <td style="color:var(--muted)">{{ $o->created_at->format('d M Y H:i') }}</td>
          <td><a class="btn btn-sm btn-blue" href="{{ route('admin.orders.show', $o) }}">Detail</a></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="pag">{{ $orders->links() }}</div>
  @endif
</div>
@endsection
