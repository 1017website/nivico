@extends('layouts.app')
@section('title', 'Dashboard Member — NIVICO Electronic Mart')

@section('content')
<div class="account-wrap">
  <div class="account-hero">
    <div>
      <span class="account-eyebrow">MEMBER AREA</span>
      <h1>Halo, {{ auth()->user()->first_name }}!</h1>
      <p>Pantau status pembayaran dan seluruh pesanan NIVICO Anda dari satu halaman.</p>
    </div>
    <a class="account-shop-btn" href="{{ route('products.index') }}">Belanja Produk</a>
  </div>

  <div class="account-stats">
    <div class="account-stat"><span>Total Pesanan</span><strong>{{ $summary['total'] }}</strong></div>
    <div class="account-stat"><span>Menunggu Pembayaran</span><strong>{{ $summary['awaiting_payment'] }}</strong></div>
    <div class="account-stat"><span>Sedang Diproses</span><strong>{{ $summary['in_progress'] }}</strong></div>
    <div class="account-stat"><span>Selesai</span><strong>{{ $summary['completed'] }}</strong></div>
  </div>

  <section class="account-panel">
    <div class="account-panel-head">
      <div>
        <h2>Riwayat Pesanan</h2>
        <p>Detail transaksi dan status pembayaran terbaru.</p>
      </div>
    </div>

    @if($orders->isEmpty())
      <div class="account-empty">
        <strong>Belum ada pesanan</strong>
        <p>Produk yang Anda checkout akan tampil di sini.</p>
        <a href="{{ route('products.index') }}">Lihat katalog produk</a>
      </div>
    @else
      <div class="account-order-list">
        @foreach($orders as $order)
          <article class="account-order">
            <div class="account-order-main">
              <span class="account-order-date">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
              <strong>#{{ $order->order_number }}</strong>
              <small>{{ $order->items_count }} produk · {{ $order->shipping_method }}</small>
            </div>
            <div class="account-order-status">
              <span class="pay-status ps-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span>
              <small>{{ $order->statusLabel() }}</small>
            </div>
            <div class="account-order-total">
              <small>Total</small>
              <strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
            </div>
            <a class="account-order-link" href="{{ route('payment.show', $order->order_number) }}">Lihat Detail</a>
          </article>
        @endforeach
      </div>
      <div class="account-pagination">{{ $orders->links('vendor.pagination.nivico') }}</div>
    @endif
  </section>
</div>
@endsection
