@extends('layouts.admin')
@section('title', 'Sistem')
@section('heading', 'Sistem')

@section('content')

<div class="panel" style="margin-bottom:20px">
  <div class="panel-hd"><h2>Perintah Sistem</h2><span class="badge b-cancelled">Khusus Super Admin</span></div>
  <div style="padding:18px 20px;color:var(--muted);font-size:13px;line-height:1.6">
    Jalankan perintah pemeliharaan langsung dari panel. Berguna saat tidak punya akses SSH di shared hosting (cPanel).
    Setiap perintah tercatat di <strong>Log Aktivitas</strong>.
  </div>
</div>

@php $res = session('cmd_result'); @endphp
@if($res)
<div class="panel" style="margin-bottom:20px;border-left:4px solid {{ $res['ok'] ? 'var(--green)' : 'var(--red)' }}">
  <div class="panel-hd">
    <h2>{{ $res['ok'] ? '✓' : '✗' }} {{ $res['label'] }} <span style="font-weight:500;color:var(--muted);font-size:12px">(exit code {{ $res['exit'] }})</span></h2>
  </div>
  <pre style="margin:0;padding:16px 20px;background:#0f172a;color:#e2e8f0;font-size:12.5px;line-height:1.55;overflow:auto;border-radius:0 0 var(--radius) var(--radius);white-space:pre-wrap;word-break:break-word">{{ $res['output'] }}</pre>
</div>
@endif

<div class="sys-grid">
  @foreach($commands as $key => $c)
    <div class="sys-card">
      <div class="sys-top">
        <h3>{{ $c['label'] }}</h3>
        <code>php artisan {{ $c['cmd'] }}</code>
      </div>
      <p>{{ $c['desc'] }}</p>
      <form method="POST" action="{{ route('admin.system.run') }}" onsubmit="return confirm('Jalankan: php artisan {{ $c['cmd'] }} ?')">
        @csrf
        <input type="hidden" name="command" value="{{ $key }}">
        <button class="btn btn-blue btn-sm" type="submit"><i class="fa-solid fa-play"></i> Jalankan</button>
      </form>
    </div>
  @endforeach
</div>

@push('styles')
<style>
.sys-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.sys-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:10px}
.sys-top h3{font-size:15px;font-weight:800;margin-bottom:4px}
.sys-top code{font-size:11.5px;background:var(--blue-soft);color:var(--blue);padding:3px 8px;border-radius:6px;font-family:monospace}
.sys-card p{font-size:12.5px;color:var(--muted);line-height:1.55;flex:1}
</style>
@endpush
@endsection
