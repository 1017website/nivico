@extends('layouts.admin')
@section('title', 'Detail Trace')
@section('heading', 'Detail Trace Integrasi')

@section('content')
<div style="margin-bottom:16px">
  <a class="btn btn-gray btn-sm" href="{{ route('admin.integration-logs.index') }}"><i class="fa-solid fa-arrow-left"></i> Kembali ke Log</a>
</div>

<div class="trace-detail-grid">
  <div class="panel">
    <div class="panel-hd">
      <div>
        <h2>{{ $integrationLog->channelLabel() }} · {{ $integrationLog->eventLabel() }}</h2>
        <div class="sub">Trace #{{ $integrationLog->id }}</div>
      </div>
      <span class="trace-status {{ $integrationLog->statusClass() }}">{{ $integrationLog->statusLabel() }}</span>
    </div>
    <dl class="trace-meta">
      <div><dt>Waktu</dt><dd>{{ $integrationLog->created_at->format('d M Y, H:i:s') }}</dd></div>
      <div><dt>Pesanan</dt><dd>
        @if($integrationLog->order)
          <a href="{{ route('admin.orders.show', $integrationLog->order_number) }}">{{ $integrationLog->order_number }}</a>
        @elseif($integrationLog->order_number)
          {{ $integrationLog->order_number }}
        @else
          —
        @endif
      </dd></div>
      <div><dt>Penerima</dt><dd>{{ $integrationLog->recipient ?: '—' }}</dd></div>
      <div><dt>Referensi</dt><dd>{{ $integrationLog->reference ?: '—' }}</dd></div>
      <div><dt>HTTP Status</dt><dd>{{ $integrationLog->http_status ?: '—' }}</dd></div>
      <div><dt>IP Pengirim</dt><dd>{{ $integrationLog->ip_address ?: '—' }}</dd></div>
    </dl>
    <div class="trace-note">
      <span>Keterangan</span>
      <p>{{ $integrationLog->message ?: 'Tidak ada keterangan.' }}</p>
    </div>
  </div>

  <div class="panel">
    <div class="panel-hd"><h2>Data Teknis Aman</h2><span class="chip">Rahasia disembunyikan</span></div>
    <pre class="trace-json">{{ json_encode($integrationLog->context ?: new stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
  </div>
</div>

@push('styles')
<style>
.trace-detail-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(360px,.85fr);gap:18px}
.trace-status{display:inline-flex;padding:6px 11px;border-radius:999px;font-size:11px;font-weight:800}.trace-ok{background:#dcfce7;color:#166534}.trace-info{background:#dbeafe;color:#1e40af}.trace-warn{background:#fef3c7;color:#92400e}.trace-error{background:#fee2e2;color:#991b1b}.trace-neutral{background:#f1f5f9;color:#475569}
.trace-meta{display:grid;grid-template-columns:1fr 1fr;padding:6px 22px}.trace-meta div{padding:14px 0;border-bottom:1px solid var(--border)}.trace-meta div:nth-child(odd){padding-right:18px}.trace-meta dt{font-size:10.5px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:5px}.trace-meta dd{font-size:12.5px;font-weight:650;word-break:break-word}.trace-meta a{color:var(--blue)}
.trace-note{padding:18px 22px}.trace-note span{font-size:10.5px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)}.trace-note p{margin-top:7px;line-height:1.65;color:#334155}
.trace-json{margin:0;padding:20px 22px;background:#0f172a;color:#dbeafe;min-height:280px;overflow:auto;font:12px/1.65 Consolas,monospace;white-space:pre-wrap;word-break:break-word}
@media(max-width:900px){.trace-detail-grid{grid-template-columns:1fr}}
@media(max-width:560px){.trace-meta{grid-template-columns:1fr}.trace-meta div:nth-child(odd){padding-right:0}}
</style>
@endpush
@endsection
