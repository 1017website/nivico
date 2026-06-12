@extends('layouts.app')
@section('title', 'Pembayaran — NIVICO')

@section('content')
<div class="pay-wrap">
  <div class="pay-card">
    <span class="pay-status ps-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span>
    <h2>Pembayaran Pesanan</h2>
    <p style="color:var(--muted);font-size:13.5px">Nomor Pesanan: <strong style="color:var(--text)">#{{ $order->order_number }}</strong></p>
    <div class="pay-total">Rp{{ number_format($order->total, 0, ',', '.') }}</div>

    @if($order->isPaid())
      <div style="background:#dcfce7;color:#166534;border-radius:8px;padding:14px;font-size:13.5px;font-weight:600">
        ✓ Pembayaran sudah lunas. Pesanan Anda sedang kami proses.
      </div>
      <a class="btn-pay-now" href="{{ route('order.success', $order->order_number) }}" style="display:block;text-align:center;text-decoration:none;margin-top:16px">Lihat Detail Pesanan</a>

    @elseif($order->payment_gateway === 'midtrans')
      {{-- ── MIDTRANS SNAP ── --}}
      @if($snapToken)
        <p style="font-size:13.5px;color:var(--muted);margin-bottom:14px">Klik tombol di bawah untuk menyelesaikan pembayaran melalui Midtrans (kartu, e-wallet, VA bank, QRIS, dan lainnya).</p>
        <button class="btn-pay-now" id="pay-button">💳 Bayar Sekarang</button>
        @push('scripts')
        <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script>
        document.getElementById('pay-button').addEventListener('click', function () {
          snap.pay(@json($snapToken), {
            onSuccess: function(){ window.location = @json(route('order.success', $order->order_number)); },
            onPending: function(){ toast('Menunggu pembayaran Anda...'); setTimeout(()=>location.reload(),1500); },
            onError:   function(){ toast('✗ Pembayaran gagal, coba lagi.'); },
            onClose:   function(){ toast('Anda menutup popup sebelum menyelesaikan pembayaran.'); }
          });
        });
        </script>
        @endpush
      @else
        <div style="background:#fef3c7;color:#92400e;border-radius:8px;padding:14px;font-size:13px">
          Midtrans belum dikonfigurasi (Server/Client Key kosong). Isi kredensial di <code>.env</code> untuk mengaktifkan pembayaran otomatis.
        </div>
      @endif

    @else
      {{-- ── MANUAL TRANSFER ── --}}
      <p style="font-size:13.5px;color:var(--muted);margin-bottom:14px">Silakan transfer sesuai nominal di atas ke salah satu rekening berikut, lalu unggah bukti transfer.</p>

      @if($order->bankAccount)
        <div class="bank-box">
          <div class="bank-logo">🏦</div>
          <div>
            <div class="bank-no" id="bankno">{{ $order->bankAccount->account_number }}</div>
            <div class="bank-meta">{{ $order->bankAccount->bank_name }} — a.n. {{ $order->bankAccount->account_holder }}</div>
          </div>
          <button class="copy-btn" type="button" onclick="navigator.clipboard.writeText('{{ $order->bankAccount->account_number }}');toast('✓ Nomor rekening disalin')">Salin</button>
        </div>
      @endif

      @if($order->payment_proof)
        <div style="background:#dbeafe;color:#1e40af;border-radius:8px;padding:12px;font-size:13px;margin-top:6px">
          ✓ Bukti transfer sudah diunggah. Menunggu verifikasi admin.
        </div>
        <img class="proof-thumb" src="{{ $order->payment_proof }}" alt="bukti transfer">
      @endif

      <form method="POST" action="{{ route('payment.proof', $order->order_number) }}" enctype="multipart/form-data">
        @csrf
        <div class="upload-box">
          <div style="font-size:13px;font-weight:600">{{ $order->payment_proof ? 'Ganti Bukti Transfer' : 'Unggah Bukti Transfer' }}</div>
          <div style="font-size:11.5px;color:var(--muted)">JPG/PNG/WEBP, maks. 4 MB</div>
          <input type="file" name="proof" accept="image/*" required>
        </div>
        <button class="btn-pay-now" type="submit" style="margin-top:12px">📤 Kirim Bukti Transfer</button>
      </form>
    @endif
  </div>

  <div class="pay-card">
    <h2 style="font-size:16px">Ringkasan</h2>
    @foreach($order->items as $it)
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
        <span>{{ $it->product_name }} × {{ $it->qty }}</span>
        <span style="font-weight:600">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</span>
      </div>
    @endforeach
    <div style="border-top:1px solid var(--border);margin-top:8px;padding-top:8px">
      <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--muted);margin-bottom:4px"><span>Subtotal</span><span>Rp{{ number_format($order->subtotal,0,',','.') }}</span></div>
      <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--muted);margin-bottom:4px"><span>Ongkir ({{ $order->shipping_method }})</span><span>Rp{{ number_format($order->shipping_cost,0,',','.') }}</span></div>
      @if($order->discount)<div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--green);margin-bottom:4px"><span>Diskon</span><span>−Rp{{ number_format($order->discount,0,',','.') }}</span></div>@endif
      <div style="display:flex;justify-content:space-between;font-weight:800;color:var(--navy);border-top:1px solid var(--border);padding-top:8px;margin-top:4px"><span>Total</span><span>Rp{{ number_format($order->total,0,',','.') }}</span></div>
    </div>
  </div>
</div>
@endsection
