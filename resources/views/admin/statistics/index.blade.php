@extends('layouts.admin')
@section('title', 'Statistik Kunjungan')
@section('heading', 'Statistik Kunjungan')

@section('content')

{{-- Filter rentang waktu --}}
<div class="content-tabs" style="display:flex;gap:6px;margin-bottom:18px">
  @foreach([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari'] as $r => $lbl)
    <a href="{{ route('admin.statistics.index', ['range' => $r]) }}" class="ctab {{ $range === $r ? 'on' : '' }}">{{ $lbl }}</a>
  @endforeach
</div>

{{-- Kartu ringkasan --}}
<div class="cards">
  <div class="card"><div class="lbl"><span class="ci"><i class="fa-solid fa-eye"></i></span> Total Kunjungan</div><div class="val">{{ number_format($totalVisits) }}</div><div class="sub" style="color:var(--muted)">dalam {{ $range }} hari terakhir</div></div>
  <div class="card"><div class="lbl"><span class="ci" style="background:#dcfce7;color:#166534"><i class="fa-solid fa-user-group"></i></span> Pengunjung Unik</div><div class="val">{{ number_format($uniqueVisitors) }}</div><div class="sub" style="color:var(--muted)">berdasarkan perangkat</div></div>
  <div class="card"><div class="lbl"><span class="ci" style="background:#e0e7ff;color:#3730a3"><i class="fa-solid fa-calendar-day"></i></span> Kunjungan Hari Ini</div><div class="val">{{ number_format($todayVisits) }}</div><div class="sub" style="color:var(--muted)">sejak 00:00</div></div>
  <div class="card"><div class="lbl"><span class="ci" style="background:#fef3c7;color:#92400e"><i class="fa-solid fa-layer-group"></i></span> Halaman / Sesi</div><div class="val">{{ $avgPerSession }}</div><div class="sub" style="color:var(--muted)">rata-rata per kunjungan</div></div>
</div>

{{-- Grafik kunjungan harian (CSS bar chart, tanpa library) --}}
<div class="panel" style="margin-top:18px">
  <div class="panel-hd"><h2>Tren Kunjungan Harian</h2></div>
  <div style="padding:20px">
    @php $maxV = max($chartData ?: [0]); $maxV = $maxV > 0 ? $maxV : 1; @endphp
    <div class="bars">
      @foreach($chartData as $i => $v)
        <div class="bar-col" title="{{ $chartLabels[$i] }}: {{ $v }} kunjungan">
          <div class="bar-val">{{ $v > 0 ? $v : '' }}</div>
          <div class="bar" style="height:{{ max(2, round($v / $maxV * 100)) }}%"></div>
          <div class="bar-lbl">{{ $chartLabels[$i] }}</div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="stat-grid">
  {{-- Device --}}
  <div class="panel">
    <div class="panel-hd"><h2><i class="fa-solid fa-mobile-screen"></i> Perangkat</h2></div>
    <div style="padding:18px">
      @php
        $deviceLabels = ['desktop' => '🖥️ Desktop', 'mobile' => '📱 Mobile', 'tablet' => '📲 Tablet'];
        $deviceTotal = array_sum($byDevice) ?: 1;
      @endphp
      @forelse($byDevice as $dev => $tot)
        @php $pct = round($tot / $deviceTotal * 100); @endphp
        <div class="prog-row">
          <div class="prog-top"><span>{{ $deviceLabels[$dev] ?? ucfirst($dev) }}</span><span>{{ number_format($tot) }} ({{ $pct }}%)</span></div>
          <div class="prog"><div class="prog-fill" style="width:{{ $pct }}%"></div></div>
        </div>
      @empty
        <p style="color:var(--muted)">Belum ada data.</p>
      @endforelse
    </div>
  </div>

  {{-- Browser --}}
  <div class="panel">
    <div class="panel-hd"><h2><i class="fa-solid fa-compass"></i> Browser</h2></div>
    <div style="padding:18px">
      @php $browserTotal = array_sum($byBrowser) ?: 1; @endphp
      @forelse($byBrowser as $br => $tot)
        @php $pct = round($tot / $browserTotal * 100); @endphp
        <div class="prog-row">
          <div class="prog-top"><span>{{ $br }}</span><span>{{ number_format($tot) }} ({{ $pct }}%)</span></div>
          <div class="prog"><div class="prog-fill" style="width:{{ $pct }}%;background:var(--green)"></div></div>
        </div>
      @empty
        <p style="color:var(--muted)">Belum ada data.</p>
      @endforelse
    </div>
  </div>

  {{-- Platform / OS --}}
  <div class="panel">
    <div class="panel-hd"><h2><i class="fa-solid fa-desktop"></i> Sistem Operasi</h2></div>
    <div style="padding:18px">
      @php $platTotal = array_sum($byPlatform) ?: 1; @endphp
      @forelse($byPlatform as $pl => $tot)
        @php $pct = round($tot / $platTotal * 100); @endphp
        <div class="prog-row">
          <div class="prog-top"><span>{{ $pl }}</span><span>{{ number_format($tot) }} ({{ $pct }}%)</span></div>
          <div class="prog"><div class="prog-fill" style="width:{{ $pct }}%;background:#7c3aed"></div></div>
        </div>
      @empty
        <p style="color:var(--muted)">Belum ada data.</p>
      @endforelse
    </div>
  </div>

  {{-- Sumber traffic --}}
  <div class="panel">
    <div class="panel-hd"><h2><i class="fa-solid fa-arrow-right-to-bracket"></i> Sumber Traffic</h2></div>
    <div style="padding:18px">
      <div class="prog-row">
        <div class="prog-top"><span>🔗 Langsung / Internal</span><span>{{ number_format($directCount) }}</span></div>
      </div>
      @forelse($topReferrers as $ref)
        <div class="prog-row">
          <div class="prog-top"><span>{{ $ref->referrer }}</span><span>{{ number_format($ref->total) }}</span></div>
        </div>
      @empty
        <p style="color:var(--muted);margin-top:6px">Belum ada rujukan dari situs lain.</p>
      @endforelse
    </div>
  </div>
</div>

{{-- Halaman populer --}}
<div class="panel" style="margin-top:18px">
  <div class="panel-hd"><h2><i class="fa-solid fa-fire"></i> Halaman Terpopuler</h2></div>
  @if($topPages->isEmpty())
    <div style="padding:20px;color:var(--muted)">Belum ada data kunjungan.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>#</th><th>Halaman</th><th style="text-align:right">Kunjungan</th></tr></thead>
    <tbody>
      @foreach($topPages as $i => $p)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><a href="{{ url($p->url) }}" target="_blank" style="color:var(--blue)">/{{ $p->url }}</a></td>
          <td style="text-align:right;font-weight:600">{{ number_format($p->total) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>

@push('styles')
<style>
.content-tabs .ctab{padding:8px 15px;border-radius:9px;font-size:13px;font-weight:600;color:var(--muted);background:#fff;border:1px solid var(--border);transition:all .15s}
.content-tabs .ctab:hover{border-color:var(--blue);color:var(--blue)}
.content-tabs .ctab.on{background:var(--blue);color:#fff;border-color:var(--blue)}
.bars{display:flex;align-items:flex-end;gap:4px;height:200px;overflow-x:auto;padding-top:18px}
.bar-col{flex:1;min-width:22px;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end}
.bar-val{font-size:10px;color:var(--muted);margin-bottom:3px;height:14px}
.bar{width:70%;max-width:34px;background:linear-gradient(180deg,var(--blue),#60a5fa);border-radius:5px 5px 0 0;min-height:2px;transition:opacity .15s}
.bar-col:hover .bar{opacity:.75}
.bar-lbl{font-size:9.5px;color:var(--muted);margin-top:6px;white-space:nowrap;transform:rotate(-45deg);transform-origin:center;height:24px}
.stat-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:18px}
.prog-row{margin-bottom:13px}
.prog-top{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px;font-weight:500}
.prog{height:8px;background:#eef2f7;border-radius:6px;overflow:hidden}
.prog-fill{height:100%;background:var(--blue);border-radius:6px;transition:width .3s}
@media(max-width:880px){.stat-grid{grid-template-columns:1fr}}
</style>
@endpush
@endsection
