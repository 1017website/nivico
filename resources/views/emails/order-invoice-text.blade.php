NIVICO ELECTRONIC MART
==================================================

INVOICE #{{ $order->order_number }}
STATUS: {{ $isPaid ? 'PAID / LUNAS' : 'UNPAID / BELUM DIBAYAR' }}

Halo {{ $order->recipient_name }},

@if($isPaid)
Pembayaran Anda telah diterima dan pesanan akan segera kami proses.
@else
Pesanan Anda telah dibuat. Silakan selesaikan pembayaran agar pesanan dapat segera kami proses.
@endif

RINCIAN PESANAN
--------------------------------------------------
@foreach($order->items as $item)
{{ $item->product_name }} x{{ $item->qty }} — Rp{{ number_format($item->subtotal, 0, ',', '.') }}
@endforeach

Subtotal: Rp{{ number_format($order->subtotal, 0, ',', '.') }}
Ongkir ({{ $order->shipping_method }}): Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}
@if($order->discount > 0)
Diskon: -Rp{{ number_format($order->discount, 0, ',', '.') }}
@endif
@if($order->service_fee > 0)
Biaya Layanan: Rp{{ number_format($order->service_fee, 0, ',', '.') }}
@endif
TOTAL: Rp{{ number_format($order->total, 0, ',', '.') }}

METODE PEMBAYARAN: {{ strtoupper($order->payment_gateway ?: $order->payment_method) }}
@if($isPaid)
LUNAS PADA: {{ optional($order->paid_at)->format('d M Y, H:i') ?: 'Waktu konfirmasi pembayaran' }}
@else
@if($order->expires_at)
BATAS PEMBAYARAN: {{ $order->expires_at->format('d M Y, H:i') }}
@endif
LINK PEMBAYARAN: {{ route('payment.show', $order->order_number) }}
@endif

DIKIRIM KEPADA
{{ $order->recipient_name }} — {{ $order->phone }}
{{ $order->address }}@if($order->district), {{ $order->district }}@endif @if($order->city), {{ $order->city }}@endif @if($order->province), {{ $order->province }}@endif @if($order->postal_code) {{ $order->postal_code }}@endif

Terima kasih telah berbelanja di NIVICO Electronic Mart.
