@extends('layouts.app')
@section('title', 'Pesanan Berhasil — NIVICO')

@section('content')
<div class="suc-wrap">
  <div class="suc-card">
    <div class="suc-ico">✅</div>
    <h2>Pesanan Berhasil!</h2>
    <p>Terima kasih telah berbelanja di NIVICO Electronic Mart. Pesanan Anda sedang diproses.</p>
    <div class="ord-num"><small>Nomor Pesanan Anda</small><strong>#{{ $order->order_number }}</strong></div>

    <div style="background:#f9fafb;border-radius:8px;padding:14px;margin-bottom:16px;text-align:left">
      @foreach($order->items as $it)
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:6px">
          <span>{{ $it->product_name }}@if($it->variation_name) ({{ $it->variation_name }})@endif × {{ $it->qty }}</span>
          <span style="font-weight:600">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</span>
        </div>
      @endforeach
      <div style="border-top:1px solid var(--border);margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;font-weight:800;color:var(--navy)">
        <span>Total Bayar</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span>
      </div>
    </div>

    <div style="background:#eff6ff;border-radius:8px;padding:14px;margin-bottom:20px;font-size:12.5px;color:var(--muted);line-height:1.7;text-align:left">
      📧 Konfirmasi pesanan telah dikirim ke email Anda.<br>
      📦 Estimasi pengiriman: <strong style="color:var(--text)">2–3 hari kerja</strong><br>
      💬 Info lebih lanjut: <strong style="color:var(--text)">(031) 123-4567</strong>
    </div>
    <a class="btn-home" href="{{ route('home') }}" style="display:inline-block;text-decoration:none">Kembali ke Beranda</a>
  </div>
</div>
@endsection
