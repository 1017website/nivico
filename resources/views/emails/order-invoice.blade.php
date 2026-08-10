<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light">
<title>Invoice {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f5fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a">
@php
  $paymentLabel = match ($order->payment_gateway) {
      'duitku' => 'Pembayaran Otomatis (Duitku)',
      'midtrans' => 'Pembayaran Otomatis (Midtrans)',
      default => 'Transfer Bank Manual',
  };
  $paymentReference = $order->duitku_reference ?: $order->midtrans_transaction_id;
@endphp

<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent">
  {{ $isPaid ? 'Pembayaran telah diterima. Invoice pesanan Anda sudah lunas.' : 'Invoice pesanan telah dibuat dan menunggu pembayaran.' }}
</div>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background-color:#f3f5fb">
  <tr>
    <td align="center" style="padding:32px 12px">
      <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:640px">
        <tr>
          <td style="background:#ffffff;border:1px solid #e5e9f2;border-bottom:0;border-radius:16px 16px 0 0;padding:20px 28px">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
              <tr>
                <td valign="middle">
                  <img src="{{ $message->embed(public_path('images/nivico-email-logo.png')) }}" width="250" alt="NIVICO Electronic Mart" style="display:block;width:250px;max-width:100%;height:auto;border:0">
                </td>
                <td align="right" valign="middle" style="font-size:11px;color:#64748b;line-height:1.5">
                  INVOICE<br>
                  <strong style="font-size:12px;color:#182a82">{{ $order->order_number }}</strong>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="background:#182a82;padding:30px 28px">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
              <tr>
                <td>
                  <div style="display:inline-block;background:{{ $isPaid ? '#dcfce7' : '#fef3c7' }};color:{{ $isPaid ? '#166534' : '#92400e' }};font-size:11px;font-weight:800;letter-spacing:.8px;padding:6px 12px;border-radius:999px">
                    {{ $isPaid ? '✓ PAID' : '● UNPAID' }}
                  </div>
                  <h1 style="margin:16px 0 7px;color:#ffffff;font-size:24px;line-height:1.25">
                    {{ $isPaid ? 'Pembayaran berhasil diterima' : 'Pesanan berhasil dibuat' }}
                  </h1>
                  <p style="margin:0;color:#cbd5ff;font-size:13px;line-height:1.65">
                    @if($isPaid)
                      Terima kasih, {{ $order->recipient_name }}. Pembayaran Anda telah terkonfirmasi dan pesanan akan segera kami proses.
                    @else
                      Halo {{ $order->recipient_name }}, selesaikan pembayaran agar pesanan dapat segera kami proses.
                    @endif
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;border-left:1px solid #e5e9f2;border-right:1px solid #e5e9f2;padding:28px">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8faff;border:1px solid #e4e9f7;border-radius:10px">
              <tr>
                <td width="34%" style="padding:14px 16px;border-right:1px solid #e4e9f7">
                  <div style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:4px">Tanggal Pesanan</div>
                  <strong style="font-size:12px;color:#0f172a">{{ $order->created_at->format('d M Y, H:i') }}</strong>
                </td>
                <td width="33%" style="padding:14px 16px;border-right:1px solid #e4e9f7">
                  <div style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:4px">Metode Bayar</div>
                  <strong style="font-size:12px;color:#0f172a">{{ $paymentLabel }}</strong>
                </td>
                <td width="33%" style="padding:14px 16px">
                  <div style="font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:4px">Status</div>
                  <strong style="font-size:12px;color:{{ $isPaid ? '#15803d' : '#b45309' }}">{{ $isPaid ? 'LUNAS' : 'BELUM DIBAYAR' }}</strong>
                </td>
              </tr>
            </table>

            <h2 style="font-size:14px;margin:28px 0 10px;color:#0f172a">Rincian Pesanan</h2>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse">
              <thead>
                <tr>
                  <th align="left" style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;padding:9px 0;border-bottom:1px solid #dfe4ee">Produk</th>
                  <th align="center" width="52" style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;padding:9px 0;border-bottom:1px solid #dfe4ee">Qty</th>
                  <th align="right" width="120" style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;padding:9px 0;border-bottom:1px solid #dfe4ee">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                @foreach($order->items as $item)
                <tr>
                  <td style="font-size:13px;padding:13px 0;border-bottom:1px solid #edf0f5;line-height:1.45">
                    <strong>{{ $item->product_name }}</strong>
                    @if($item->sku)
                      <br><span style="font-size:10px;color:#94a3b8">SKU: {{ $item->sku }}</span>
                    @endif
                  </td>
                  <td align="center" style="font-size:12px;padding:13px 0;border-bottom:1px solid #edf0f5">{{ $item->qty }}</td>
                  <td align="right" style="font-size:12px;font-weight:700;padding:13px 0;border-bottom:1px solid #edf0f5">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px">
              <tr>
                <td width="46%">&nbsp;</td>
                <td>
                  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                      <td style="font-size:12px;color:#64748b;padding:4px 0">Subtotal</td>
                      <td align="right" style="font-size:12px;padding:4px 0">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                      <td style="font-size:12px;color:#64748b;padding:4px 0">Ongkir</td>
                      <td align="right" style="font-size:12px;padding:4px 0">Rp{{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount > 0)
                    <tr>
                      <td style="font-size:12px;color:#15803d;padding:4px 0">Diskon</td>
                      <td align="right" style="font-size:12px;color:#15803d;padding:4px 0">−Rp{{ number_format($order->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($order->service_fee > 0)
                    <tr>
                      <td style="font-size:12px;color:#64748b;padding:4px 0">Biaya Layanan</td>
                      <td align="right" style="font-size:12px;padding:4px 0">Rp{{ number_format($order->service_fee, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                      <td style="font-size:15px;font-weight:800;padding:11px 0 0;border-top:2px solid #dfe4ee">Total</td>
                      <td align="right" style="font-size:17px;font-weight:800;color:#182a82;padding:11px 0 0;border-top:2px solid #dfe4ee">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>

            @if($isPaid)
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px">
                <tr>
                  <td style="padding:16px 18px">
                    <div style="font-size:11px;font-weight:800;color:#166534;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Pembayaran Terkonfirmasi</div>
                    <div style="font-size:13px;color:#14532d;line-height:1.55">
                      Lunas pada {{ optional($order->paid_at)->format('d M Y, H:i') ?: 'waktu konfirmasi pembayaran' }}.
                      @if($paymentReference)<br>Referensi: <strong>{{ $paymentReference }}</strong>@endif
                    </div>
                  </td>
                </tr>
              </table>
            @else
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px">
                <tr>
                  <td style="padding:16px 18px">
                    <div style="font-size:11px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px">Total yang harus dibayar</div>
                    <div style="font-size:24px;font-weight:800;color:#182a82;margin-bottom:6px">Rp{{ number_format($order->total, 0, ',', '.') }}</div>
                    <div style="font-size:12px;color:#78350f;line-height:1.55">
                      @if($order->expires_at)
                        Bayar sebelum <strong>{{ $order->expires_at->format('d M Y, H:i') }}</strong>.
                      @else
                        Segera selesaikan pembayaran agar pesanan dapat diproses.
                      @endif
                      @if($order->payment_gateway === 'manual_transfer' && $order->bankAccount)
                        <br><br><strong>{{ $order->bankAccount->bank_name }} — {{ $order->bankAccount->account_number }}</strong><br>
                        a.n. {{ $order->bankAccount->account_holder }}
                      @endif
                    </div>
                  </td>
                </tr>
              </table>
            @endif

            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:22px;background:#f8fafc;border-radius:10px">
              <tr>
                <td style="padding:16px 18px">
                  <div style="font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Dikirim Kepada</div>
                  <div style="font-size:13px;font-weight:700;margin-bottom:3px">{{ $order->recipient_name }} · {{ $order->phone }}</div>
                  <div style="font-size:12px;color:#475569;line-height:1.6">
                    {{ $order->address }}
                    @if($order->district), {{ $order->district }}@endif
                    @if($order->city), {{ $order->city }}@endif
                    @if($order->province), {{ $order->province }}@endif
                    @if($order->postal_code) {{ $order->postal_code }}@endif
                    <br>Kurir: <strong>{{ $order->shipping_method }}</strong>@if($order->shipping_etd) · Estimasi {{ $order->shipping_etd }}@endif
                  </div>
                </td>
              </tr>
            </table>

            @if(! $isPaid)
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:24px">
              <tr>
                <td align="center">
                  <a href="{{ route('payment.show', $order->order_number) }}" style="display:inline-block;background:#182a82;color:#ffffff;text-decoration:none;font-size:13px;font-weight:800;padding:14px 26px;border-radius:8px">Selesaikan Pembayaran</a>
                </td>
              </tr>
            </table>
            @endif
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;border:1px solid #e5e9f2;border-top:1px solid #edf0f5;border-radius:0 0 16px 16px;padding:20px 28px;text-align:center">
            <p style="font-size:11px;color:#94a3b8;line-height:1.65;margin:0">
              Email ini dikirim otomatis oleh NIVICO Electronic Mart.<br>
              Simpan invoice ini sebagai bukti transaksi Anda.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
