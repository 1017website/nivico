@extends('layouts.admin')
@section('title', 'Log Integrasi')
@section('heading', 'Log Email & Duitku')

@section('content')
<div class="cards trace-cards">
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-wave-square"></i></span>Trace Hari Ini</div><div class="val">{{ number_format($stats['today']) }}</div></div>
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-envelope"></i></span>Email</div><div class="val">{{ number_format($stats['emails']) }}</div></div>
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-money-check-dollar"></i></span>Callback Duitku</div><div class="val">{{ number_format($stats['callbacks']) }}</div></div>
  <div class="card"><div class="lbl"><span class="ci trace-ci-error"><i class="fa-solid fa-triangle-exclamation"></i></span>Perlu Diperiksa</div><div class="val">{{ number_format($stats['problems']) }}</div></div>
</div>

<div class="panel">
  <div class="panel-hd">
    <div>
      <h2>Jejak Integrasi</h2>
      <div class="sub">Status pengiriman invoice dan callback pembayaran Duitku yang diterima server.</div>
    </div>
  </div>
  <form class="trace-filter" method="GET" action="{{ route('admin.integration-logs.index') }}">
    <input class="inp" type="search" name="q" value="{{ request('q') }}" placeholder="Nomor pesanan, email, referensi...">
    <select class="inp" name="channel">
      <option value="">Semua channel</option>
      <option value="email" @selected(request('channel') === 'email')>Email</option>
      <option value="duitku" @selected(request('channel') === 'duitku')>Duitku</option>
    </select>
    <select class="inp" name="status">
      <option value="">Semua status</option>
      @foreach(['sent'=>'Diteruskan','simulated'=>'Mode Log','processed'=>'Berhasil','rejected'=>'Ditolak','failed'=>'Gagal','ignored'=>'Diabaikan','received'=>'Diterima','processing'=>'Diproses','skipped'=>'Dilewati'] as $value => $label)
        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
      @endforeach
    </select>
    <button class="btn btn-blue" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    @if(request()->hasAny(['q', 'channel', 'status']))
      <a class="btn btn-gray" href="{{ route('admin.integration-logs.index') }}">Reset</a>
    @endif
  </form>

  @if($logs->isEmpty())
    <div class="empty">
      <div class="ei"><i class="fa-solid fa-wave-square"></i></div>
      Belum ada trace integrasi yang sesuai.
    </div>
  @else
    <div class="table-wrap">
      <table class="trace-table">
        <thead>
          <tr><th>Waktu</th><th>Channel / Event</th><th>Pesanan</th><th>Tujuan / Referensi</th><th>Status</th><th>Keterangan</th><th></th></tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
            <tr>
              <td class="trace-time">{{ $log->created_at->format('d M Y') }}<small>{{ $log->created_at->format('H:i:s') }}</small></td>
              <td><strong>{{ $log->channelLabel() }}</strong><small>{{ $log->eventLabel() }}</small></td>
              <td>
                @if($log->order)
                  <a class="trace-link" href="{{ route('admin.orders.show', $log->order_number) }}">{{ $log->order_number }}</a>
                @elseif($log->order_number)
                  <strong>{{ $log->order_number }}</strong>
                @else
                  <span class="trace-muted">—</span>
                @endif
              </td>
              <td>
                <span class="trace-ellipsis">{{ $log->channel === 'email' ? $log->recipient : $log->reference }}</span>
                @if($log->http_status)<small>HTTP {{ $log->http_status }}</small>@endif
              </td>
              <td><span class="trace-status {{ $log->statusClass() }}">{{ $log->statusLabel() }}</span></td>
              <td class="trace-message">{{ $log->message ?: '—' }}</td>
              <td><a class="btn btn-gray btn-sm" href="{{ route('admin.integration-logs.show', $log) }}">Detail</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="pag">{{ $logs->links() }}</div>
  @endif
</div>

@push('styles')
<style>
.trace-cards{margin-bottom:20px}.trace-ci-error{background:#fee2e2!important;color:#dc2626!important}
.trace-filter{display:grid;grid-template-columns:minmax(240px,1fr) 160px 170px auto auto;gap:9px;padding:16px 22px;border-bottom:1px solid var(--border)}
.trace-table{min-width:1050px!important}.trace-table td small,.trace-table td strong{display:block}.trace-table td small{font-size:10.5px;color:var(--muted);margin-top:3px}
.trace-time{white-space:nowrap}.trace-link{color:var(--blue);font-weight:700}.trace-link:hover{text-decoration:underline}
.trace-muted{color:#94a3b8}.trace-ellipsis{display:block;max-width:210px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.trace-message{max-width:260px;color:#475569;font-size:12px;line-height:1.45}
.trace-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:10.5px;font-weight:800;white-space:nowrap}
.trace-ok{background:#dcfce7;color:#166534}.trace-info{background:#dbeafe;color:#1e40af}.trace-warn{background:#fef3c7;color:#92400e}.trace-error{background:#fee2e2;color:#991b1b}.trace-neutral{background:#f1f5f9;color:#475569}
@media(max-width:900px){.trace-filter{grid-template-columns:1fr 1fr}.trace-filter input{grid-column:1/-1}}
@media(max-width:560px){.trace-filter{grid-template-columns:1fr}.trace-filter input{grid-column:auto}.trace-filter .btn{justify-content:center}}
</style>
@endpush
@endsection
