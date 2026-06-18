@extends('layouts.admin')
@section('title', 'Detail Pesanan')
@section('heading', 'Detail Pesanan')

@section('content')
@php $statuses = ['pending'=>'Menunggu Pembayaran','paid'=>'Sudah Dibayar','processing'=>'Diproses','shipped'=>'Dikirim','completed'=>'Selesai','cancelled'=>'Dibatalkan']; @endphp
<div style="margin-bottom:18px"><a class="btn btn-sm btn-gray" href="{{ route('admin.orders.index') }}">← Kembali ke daftar</a></div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
  <div>
    <div class="panel">
      <div class="panel-hd"><h2>Pesanan #{{ $order->order_number }}</h2><span class="badge b-{{ $order->status }}">{{ $order->statusLabel() }}</span></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
          @foreach($order->items as $it)
            <tr>
              <td style="display:flex;align-items:center;gap:10px">@if($it->image)<img class="thumb" src="{{ $it->image }}" onerror="this.onerror=null;this.src='/images/placeholder-product.svg'">@endif<div>{{ $it->product_name }}@if($it->variation_name) <span style="font-size:11.5px;color:var(--muted)">({{ $it->variation_name }})</span>@endif<div style="font-size:11.5px;color:var(--muted)">{{ $it->sku }}</div></div></td>
              <td>Rp{{ number_format($it->price, 0, ',', '.') }}</td>
              <td>{{ $it->qty }}</td>
              <td style="font-weight:600">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table></div>
      <div style="padding:16px 20px;border-top:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;color:var(--muted)"><span>Subtotal</span><span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;color:var(--muted)"><span>Ongkir</span><span>Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;color:var(--green)"><span>Diskon @if($order->promo)({{ $order->promo->code }})@endif</span><span>−Rp{{ number_format($order->discount, 0, ',', '.') }}</span></div>
        <div style="display:flex;justify-content:space-between;font-weight:800;font-size:15px;border-top:1px solid var(--border);padding-top:10px;margin-top:4px"><span>Total</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
      </div>
    </div>
  </div>

  <div>
    <div class="panel">
      <div class="panel-hd"><h2>Ubah Status</h2></div>
      <div style="padding:18px">
        <form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf @method('PATCH')
          <div class="fld"><label style="font-size:12px;font-weight:600;color:var(--muted)">Status Pesanan</label><select class="inp" name="status">
            @foreach($statuses as $key => $lbl)
              <option value="{{ $key }}" @selected($order->status===$key)>{{ $lbl }}</option>
            @endforeach
          </select></div>
          <div class="fld"><label style="font-size:12px;font-weight:600;color:var(--muted)">No. Resi (opsional)</label><input class="inp" type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="No. resi pengiriman"></div>
          <button class="btn btn-blue" type="submit" style="width:100%">Perbarui Status</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-hd"><h2>Pembayaran</h2><span class="badge ps-{{ $order->payment_status }}" style="font-size:11px">{{ $order->paymentStatusLabel() }}</span></div>
      <div style="padding:18px;font-size:13px;line-height:1.8">
        <div>Metode: <strong style="text-transform:capitalize">{{ str_replace('_',' ',$order->payment_gateway) }}</strong></div>
        @if($order->bankAccount)<div>Bank: <strong>{{ $order->bankAccount->bank_name }}</strong> ({{ $order->bankAccount->account_number }})</div>@endif
        @if($order->paid_at)<div>Dibayar: <strong>{{ $order->paid_at->format('d M Y H:i') }}</strong></div>@endif
        @if($order->midtrans_payment_type)<div>Tipe Midtrans: <strong>{{ $order->midtrans_payment_type }}</strong></div>@endif

        @if($order->payment_proof)
          <div style="margin-top:10px">Bukti transfer:</div>
          <a href="{{ $order->payment_proof }}" target="_blank"><img src="{{ $order->payment_proof }}" style="max-width:100%;border-radius:8px;border:1px solid var(--border);margin-top:6px"></a>
        @endif

        @if($order->payment_gateway === 'manual_transfer' && !$order->isPaid())
          <form method="POST" action="{{ route('admin.orders.verify', $order) }}" style="display:flex;gap:8px;margin-top:14px">@csrf @method('PATCH')
            <button class="btn btn-sm" style="background:var(--green)" type="submit" name="action" value="approve">✓ Setujui Pembayaran</button>
            <button class="btn btn-sm btn-red" type="submit" name="action" value="reject">✗ Tolak</button>
          </form>
        @endif
      </div>
    </div>

    <div class="panel">
      <div class="panel-hd"><h2>Pengiriman</h2></div>
      <div style="padding:18px;font-size:13px;line-height:1.8">
        <strong>{{ $order->recipient_name }}</strong><br>
        {{ $order->phone }}<br>
        {{ $order->address }}<br>
        {{ collect([$order->district, $order->city, $order->province, $order->postal_code])->filter()->join(', ') }}<br>
        <div style="margin-top:10px;color:var(--muted)">
          <div>Kurir: <strong style="color:#111827;text-transform:uppercase">{{ $order->shipping_method }}</strong></div>
          <div>Bayar: <strong style="color:#111827;text-transform:capitalize">{{ $order->payment_method }}</strong></div>
          @if($order->note)<div style="margin-top:6px">Catatan: {{ $order->note }}</div>@endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
