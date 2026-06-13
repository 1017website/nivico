<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f5fa;font-family:Arial,Helvetica,sans-serif;color:#0f172a">
  <div style="max-width:600px;margin:0 auto;padding:24px 16px">

    <!-- Header -->
    <div style="background:#1a2b8a;border-radius:12px 12px 0 0;padding:28px 28px 22px;text-align:center">
      <div style="font-size:24px;font-weight:800;color:#fff;letter-spacing:-.5px">NIVICO</div>
      <div style="font-size:11px;color:rgba(255,255,255,.7);letter-spacing:1px;text-transform:uppercase">Electronic Mart</div>
    </div>

    <!-- Body -->
    <div style="background:#fff;padding:28px">
      @if($kind === 'paid')
        <div style="display:inline-block;background:#dcfce7;color:#166534;font-size:12px;font-weight:700;padding:5px 14px;border-radius:99px;margin-bottom:14px">✓ Pembayaran Diterima</div>
        <h1 style="font-size:20px;margin:0 0 6px">Terima kasih, pembayaran Anda lunas!</h1>
        <p style="font-size:14px;color:#64748b;margin:0 0 20px;line-height:1.6">Pesanan Anda sedang kami proses dan akan segera dikirim. Berikut rincian pesanan Anda.</p>
      @else
        <div style="display:inline-block;background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;padding:5px 14px;border-radius:99px;margin-bottom:14px">Menunggu Pembayaran</div>
        <h1 style="font-size:20px;margin:0 0 6px">Pesanan Anda telah diterima</h1>
        <p style="font-size:14px;color:#64748b;margin:0 0 20px;line-height:1.6">Silakan selesaikan pembayaran agar pesanan dapat kami proses. Berikut rincian pesanan Anda.</p>
      @endif

      <!-- Order meta -->
      <table style="width:100%;border-collapse:collapse;margin-bottom:18px">
        <tr>
          <td style="font-size:13px;color:#64748b;padding:4px 0">No. Pesanan</td>
          <td style="font-size:13px;font-weight:700;text-align:right;padding:4px 0">{{ $order->order_number }}</td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#64748b;padding:4px 0">Tanggal</td>
          <td style="font-size:13px;text-align:right;padding:4px 0">{{ $order->created_at->format('d M Y, H:i') }}</td>
        </tr>
        <tr>
          <td style="font-size:13px;color:#64748b;padding:4px 0">Penerima</td>
          <td style="font-size:13px;text-align:right;padding:4px 0">{{ $order->recipient_name }}</td>
        </tr>
      </table>

      <!-- Items -->
      <table style="width:100%;border-collapse:collapse;border-top:2px solid #e5e7eb">
        <thead>
          <tr>
            <th style="text-align:left;font-size:11px;color:#64748b;text-transform:uppercase;padding:10px 0;border-bottom:1px solid #e5e7eb">Produk</th>
            <th style="text-align:center;font-size:11px;color:#64748b;text-transform:uppercase;padding:10px 0;border-bottom:1px solid #e5e7eb">Qty</th>
            <th style="text-align:right;font-size:11px;color:#64748b;text-transform:uppercase;padding:10px 0;border-bottom:1px solid #e5e7eb">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $it)
          <tr>
            <td style="font-size:13px;padding:10px 0;border-bottom:1px solid #f1f5f9">{{ $it->product_name }}<br><span style="font-size:11px;color:#94a3b8">{{ $it->sku }} &middot; Rp{{ number_format($it->price, 0, ',', '.') }}</span></td>
            <td style="font-size:13px;text-align:center;padding:10px 0;border-bottom:1px solid #f1f5f9">{{ $it->qty }}</td>
            <td style="font-size:13px;text-align:right;padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Totals -->
      <table style="width:100%;border-collapse:collapse;margin-top:14px">
        <tr><td style="font-size:13px;color:#64748b;padding:3px 0">Subtotal</td><td style="font-size:13px;text-align:right;padding:3px 0">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
        <tr><td style="font-size:13px;color:#64748b;padding:3px 0">Ongkos Kirim ({{ $order->shipping_method }})</td><td style="font-size:13px;text-align:right;padding:3px 0">Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</td></tr>
        @if($order->discount > 0)
        <tr><td style="font-size:13px;color:#16a34a;padding:3px 0">Diskon</td><td style="font-size:13px;text-align:right;padding:3px 0;color:#16a34a">−Rp{{ number_format($order->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr><td style="font-size:16px;font-weight:800;padding:10px 0 0;border-top:2px solid #e5e7eb">Total</td><td style="font-size:16px;font-weight:800;text-align:right;padding:10px 0 0;border-top:2px solid #e5e7eb;color:#1a2b8a">Rp{{ number_format($order->total, 0, ',', '.') }}</td></tr>
      </table>

      <!-- Shipping address -->
      <div style="background:#f9fafb;border-radius:8px;padding:16px;margin-top:20px">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;margin-bottom:6px">Alamat Pengiriman</div>
        <div style="font-size:13px;line-height:1.6">{{ $order->recipient_name }} ({{ $order->phone }})<br>{{ $order->address }}@if($order->city), {{ $order->city }}@endif @if($order->postal_code) {{ $order->postal_code }}@endif</div>
      </div>

      @if($kind !== 'paid')
      <div style="text-align:center;margin-top:24px">
        <a href="{{ route('payment.show', $order->order_number) }}" style="display:inline-block;background:#1a2b8a;color:#fff;text-decoration:none;font-size:14px;font-weight:700;padding:13px 28px;border-radius:8px">Selesaikan Pembayaran</a>
      </div>
      @endif
    </div>

    <!-- Footer -->
    <div style="background:#fff;border-radius:0 0 12px 12px;border-top:1px solid #e5e7eb;padding:18px 28px;text-align:center">
      <p style="font-size:12px;color:#94a3b8;margin:0;line-height:1.6">Email ini dikirim otomatis oleh NIVICO Electronic Mart.<br>Butuh bantuan? Hubungi kami melalui WhatsApp atau email.</p>
    </div>

  </div>
</body>
</html>
