@extends('layouts.admin')
@section('title', 'Pesanan')
@section('heading', 'Pesanan')

@push('styles')
<style>
.orders-toolbar{justify-content:space-between}
.orders-toolbar form{max-width:520px}
.attention-filter{white-space:nowrap}
.attention-filter.on{background:#eef2ff;border-color:#c7d2fe;color:var(--navy)}
.attention-filter .filter-count{min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--navy);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800}
.order-unread td{background:#f5f8ff}
.order-unread td:first-child{box-shadow:inset 4px 0 0 var(--blue)}
.order-unread:hover td{background:#edf3ff}
.order-number-wrap{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.order-notice-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-size:9px;font-weight:800;letter-spacing:.25px;text-transform:uppercase;white-space:nowrap}
.order-notice-badge.payment{background:#dcfce7;color:#15803d}
.order-notice-badge::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
</style>
@endpush

@section('content')
@php $statuses = ['pending'=>'Menunggu','paid'=>'Dibayar','processing'=>'Diproses','shipped'=>'Dikirim','completed'=>'Selesai','cancelled'=>'Dibatalkan']; @endphp
<div class="toolbar orders-toolbar">
  <form method="GET" action="{{ route('admin.orders.index') }}">
    @if(request()->boolean('attention'))
      <input type="hidden" name="attention" value="1">
    @endif
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. pesanan / penerima...">
    <select class="inp" name="status" onchange="this.form.submit()" style="max-width:160px">
      <option value="">Semua Status</option>
      @foreach($statuses as $key => $lbl)
        <option value="{{ $key }}" @selected(request('status')===$key)>{{ $lbl }}</option>
      @endforeach
    </select>
    <button class="btn btn-gray" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  @if(($adminOrderNoticeCount ?? 0) > 0 || request()->boolean('attention'))
    <a class="btn btn-gray attention-filter {{ request()->boolean('attention') ? 'on' : '' }}"
       href="{{ request()->boolean('attention') ? route('admin.orders.index') : route('admin.orders.index', ['attention' => 1]) }}">
      <i class="fa-solid fa-bell"></i>
      {{ request()->boolean('attention') ? 'Tampilkan Semua' : 'Perlu Dilihat' }}
      @if(($adminOrderNoticeCount ?? 0) > 0)
        <span class="filter-count">{{ $adminOrderNoticeCount > 99 ? '99+' : $adminOrderNoticeCount }}</span>
      @endif
    </a>
  @endif
</div>

<div class="panel">
  @if($orders->isEmpty())
    <div class="empty">Belum ada pesanan.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>No. Pesanan</th><th>Penerima</th><th>Items</th><th>Total</th><th>Bayar</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
    <tbody>
      @foreach($orders as $o)
        <tr class="{{ $o->needsAdminAttention() ? 'order-unread' : '' }}">
          <td style="font-weight:700">
            <div class="order-number-wrap">
              <span>#{{ $o->order_number }}</span>
              @if($o->needsAdminAttention())
                <span class="order-notice-badge {{ $o->admin_notice_type === 'payment' ? 'payment' : '' }}">{{ $o->adminAttentionLabel() }}</span>
              @endif
            </div>
          </td>
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
