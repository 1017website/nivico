<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — NIVICO</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root{--navy:#0f2557;--navy2:#0a1b42;--blue:#2563eb;--border:#e5e7eb;--muted:#6b7280;--bg:#f1f5f9;--red:#dc2626;--green:#16a34a}
*{margin:0;padding:0;box-sizing:border-box;font-family:'DM Sans',sans-serif}
body{background:var(--bg);color:#111827;font-size:14px}
a{text-decoration:none;color:inherit}
.adm{display:flex;min-height:100vh}
/* sidebar */
.sb{width:240px;background:var(--navy);color:#cbd5e1;position:fixed;top:0;bottom:0;left:0;display:flex;flex-direction:column;z-index:50}
.sb-logo{padding:20px;font-family:'DM Serif Display',serif;font-size:20px;color:#fff;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-logo small{display:block;font-family:'DM Sans';font-size:11px;color:#94a3b8;font-weight:400}
.sb-nav{flex:1;padding:12px 0;overflow-y:auto}
.sb-nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;font-size:13.5px;font-weight:500;transition:background .15s,color .15s}
.sb-nav a:hover{background:rgba(255,255,255,.06);color:#fff}
.sb-nav a.on{background:var(--blue);color:#fff;font-weight:600}
.sb-nav .ico{font-size:16px;width:20px;text-align:center}
.sb-foot{padding:14px 20px;border-top:1px solid rgba(255,255,255,.08)}
.sb-foot a{font-size:12.5px;color:#94a3b8}
/* main */
.mn{flex:1;margin-left:240px;display:flex;flex-direction:column}
.tb{background:#fff;border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40}
.tb h1{font-size:18px;font-weight:800}
.tb-r{display:flex;align-items:center;gap:14px;font-size:13px;color:var(--muted)}
.tb-r a.viewsite{color:var(--blue);font-weight:600}
.ct{padding:28px;flex:1}
/* cards & tables */
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.card{background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px}
.card .lbl{font-size:12.5px;color:var(--muted);margin-bottom:6px}
.card .val{font-size:26px;font-weight:800;font-family:'DM Serif Display',serif;color:var(--navy)}
.card .sub{font-size:11.5px;margin-top:4px}
.panel{background:#fff;border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:20px}
.panel-hd{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.panel-hd h2{font-size:15px;font-weight:800}
table{width:100%;border-collapse:collapse}
th,td{padding:11px 16px;text-align:left;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
th{background:#f8fafc;font-weight:700;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.3px}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafbfc}
.btn{display:inline-flex;align-items:center;gap:6px;border:none;border-radius:6px;padding:8px 14px;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;background:var(--navy);color:#fff}
.btn:hover{opacity:.9}
.btn-sm{padding:6px 10px;font-size:12px}
.btn-blue{background:var(--blue)}
.btn-red{background:var(--red)}
.btn-gray{background:#fff;border:1px solid var(--border);color:#374151}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:700}
.b-pending{background:#fef3c7;color:#92400e}.b-paid{background:#dbeafe;color:#1e40af}.b-processing{background:#e0e7ff;color:#3730a3}
.b-shipped{background:#cffafe;color:#155e75}.b-completed{background:#dcfce7;color:#166534}.b-cancelled{background:#fee2e2;color:#991b1b}
.ps-unpaid,.ps-pending{background:#fef3c7;color:#92400e}.ps-paid{background:#dcfce7;color:#166534}.ps-failed,.ps-expired{background:#fee2e2;color:#991b1b}.ps-refunded{background:#e0e7ff;color:#3730a3}
.inp,select.inp,textarea.inp{border:1px solid var(--border);border-radius:6px;padding:9px 12px;font-size:13.5px;width:100%;outline:none;transition:border-color .15s}
.inp:focus{border-color:var(--navy)}
.frm-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fld{display:flex;flex-direction:column;gap:5px;margin-bottom:16px}
.fld label{font-size:12.5px;font-weight:600;color:var(--muted)}
.fld.full{grid-column:1/-1}
.toolbar{display:flex;gap:10px;align-items:center;margin-bottom:18px;flex-wrap:wrap}
.toolbar form{display:flex;gap:8px;flex:1;max-width:360px}
.pag{padding:14px 20px}
.thumb{width:42px;height:42px;border-radius:6px;object-fit:cover;border:1px solid var(--border)}
.empty{padding:48px;text-align:center;color:var(--muted)}
#toast{position:fixed;bottom:24px;right:24px;background:#1e293b;color:#fff;padding:12px 22px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transition:opacity .3s;pointer-events:none}
@media(max-width:900px){.cards{grid-template-columns:repeat(2,1fr)}.sb{width:64px}.sb-logo,.sb-nav a span,.sb-foot a span{display:none}.mn{margin-left:64px}.frm-grid{grid-template-columns:1fr}}
</style>
@stack('styles')
</head>
<body>
<div class="adm">
  <aside class="sb">
    <div class="sb-logo">NIVICO<small>Admin Panel</small></div>
    <nav class="sb-nav">
      @foreach($adminMenus ?? [] as $key => $m)
        <a href="{{ route($m['route']) }}" class="{{ request()->routeIs(str_replace('.index','',$m['route']).'*') ? 'on' : '' }}"><span class="ico">{!! $m['icon'] !!}</span><span>{{ $m['label'] }}</span></a>
      @endforeach
    </nav>
    <div class="sb-foot">
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" style="background:none;border:none;color:#94a3b8;font-size:12.5px;cursor:pointer">↩ Keluar</button>
      </form>
    </div>
  </aside>

  <div class="mn">
    <div class="tb">
      <h1>@yield('heading', 'Dashboard')</h1>
      <div class="tb-r">
        <a class="viewsite" href="{{ route('home') }}" target="_blank">↗ Lihat Toko</a>
        <span>👤 {{ auth()->user()->name }}</span>
      </div>
    </div>
    <div class="ct">
      @yield('content')
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
function toast(m){var t=document.getElementById('toast');t.textContent=m;t.style.opacity='1';clearTimeout(t._t);t._t=setTimeout(function(){t.style.opacity='0'},2600);}
function confirmDelete(f){return confirm('Yakin ingin menghapus data ini?');}
@if(session('toast'))document.addEventListener('DOMContentLoaded',function(){toast(@json(session('toast')))});@endif
@if(session('error'))document.addEventListener('DOMContentLoaded',function(){toast(@json('✗ '.session('error')))});@endif
@if($errors->any())document.addEventListener('DOMContentLoaded',function(){toast(@json('✗ '.$errors->first()))});@endif
</script>
@stack('scripts')
</body>
</html>
