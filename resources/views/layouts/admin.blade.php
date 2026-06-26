<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — NIVICO</title>
<link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
<style>
:root{
  --navy:#0f1d6b;--navy2:#1a2b8a;--blue:#2563eb;--blue-soft:#eff4ff;
  --border:#e8ecf3;--muted:#6b7280;--bg:#f5f7fb;--ink:#0f172a;
  --red:#dc2626;--green:#16a34a;--amber:#d97706;
  --radius:14px;--shadow:0 1px 3px rgba(16,24,40,.06),0 1px 2px rgba(16,24,40,.04);
  --shadow-lg:0 8px 24px rgba(16,24,40,.08);
}
*{margin:0;padding:0;box-sizing:border-box;font-family:'Plus Jakarta Sans',system-ui,sans-serif}
body{background:var(--bg);color:var(--ink);font-size:14px;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
.adm{display:flex;min-height:100vh}

/* ── SIDEBAR (putih, aksen navy) ── */
.sb{width:248px;background:#fff;color:#475569;position:fixed;top:0;bottom:0;left:0;display:flex;flex-direction:column;z-index:50;border-right:1px solid var(--border)}
.sb-logo{padding:22px 22px 18px;display:flex;align-items:center;gap:11px;border-bottom:1px solid var(--border)}
.sb-logo .mark{width:38px;height:38px;border-radius:11px;background:var(--navy);display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:15px;flex-shrink:0}
.sb-logo .txt{font-weight:800;font-size:17px;color:var(--navy);letter-spacing:-.3px;line-height:1}
.sb-logo .txt small{display:block;font-size:10.5px;color:#94a3b8;font-weight:500;letter-spacing:.5px;margin-top:2px}
.sb-logo-img{max-height:42px;max-width:180px;width:auto;object-fit:contain}
.sb-logo-mark{width:38px;height:38px;border-radius:11px;object-fit:contain;flex-shrink:0;background:#fff;border:1px solid var(--border)}
.sb-nav{flex:1;padding:10px 12px;overflow-y:auto}
.sb-nav::-webkit-scrollbar{width:5px}.sb-nav::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:9px}
.sb-group{font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.7px;padding:14px 12px 6px}
.sb-nav a{display:flex;align-items:center;gap:12px;padding:10px 12px;font-size:13.5px;font-weight:500;border-radius:10px;color:#475569;transition:all .15s;margin-bottom:2px}
.sb-nav a:hover{background:#f1f5f9;color:var(--navy)}
.sb-nav a.on{background:var(--navy);color:#fff;font-weight:600;box-shadow:0 4px 12px rgba(15,29,107,.25)}
.sb-nav a .ico{width:20px;text-align:center;font-size:15px;flex-shrink:0;color:inherit}
.sb-nav a:hover .ico{color:var(--navy)}
.sb-nav a.on .ico{color:#fff}
.sb-foot{padding:14px 16px;border-top:1px solid var(--border)}
.sb-foot button{display:flex;align-items:center;gap:10px;background:none;border:none;color:#64748b;font-size:13px;cursor:pointer;padding:6px;font-weight:500;transition:color .15s}
.sb-foot button:hover{color:var(--red)}

/* ── MAIN ── */
.mn{flex:1;margin-left:248px;display:flex;flex-direction:column;min-width:0}
.tb{background:rgba(255,255,255,.85);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);padding:15px 30px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40}
.tb h1{font-size:19px;font-weight:800;letter-spacing:-.4px}
.tb-r{display:flex;align-items:center;gap:16px;font-size:13px;color:var(--muted)}
.tb-r .viewsite{color:var(--navy);font-weight:600;display:inline-flex;align-items:center;gap:6px}
.tb-r .who{display:inline-flex;align-items:center;gap:8px;font-weight:600;color:var(--ink)}
.tb-r .who .av{width:30px;height:30px;border-radius:50%;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.sb-toggle{display:none;background:none;border:none;font-size:20px;cursor:pointer;color:var(--ink);margin-right:6px}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(15,29,107,.4);z-index:45;backdrop-filter:blur(2px)}
.sb-overlay.show{display:block}
.ct{padding:30px;flex:1}

/* ── CARDS / STATS ── */
.cards{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:26px}
.card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);transition:box-shadow .2s,transform .2s}
.card:hover{box-shadow:var(--shadow-lg);transform:translateY(-1px)}
.card .lbl{font-size:12.5px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:8px}
.card .lbl .ci{width:30px;height:30px;border-radius:9px;background:var(--blue-soft);color:var(--blue);display:flex;align-items:center;justify-content:center;font-size:14px}
.card .val{font-size:27px;font-weight:800;letter-spacing:-.5px}
.card .sub{font-size:11.5px;margin-top:5px;color:var(--muted)}

/* ── PANEL ── */
.panel{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:22px;box-shadow:var(--shadow)}
.panel-hd{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.panel-hd h2{font-size:15.5px;font-weight:800}
.panel-hd .sub{font-size:12.5px;color:var(--muted);font-weight:400;margin-top:2px}

/* ── TABLE ── */
.table-wrap{width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse}
.table-wrap table{min-width:560px}
th,td{padding:13px 18px;text-align:left;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
th{background:#fafbfd;font-weight:700;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px}
tr:last-child td{border-bottom:none}
tbody tr{transition:background .12s}
tbody tr:hover td{background:#fafbff}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:7px;border:none;border-radius:10px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;background:var(--navy);color:#fff}
.btn:hover{background:var(--navy2);transform:translateY(-1px)}
.btn-sm{padding:7px 11px;font-size:12px;border-radius:8px}
.btn-blue{background:var(--blue)}.btn-blue:hover{background:#1d4ed8}
.btn-red{background:var(--red)}.btn-red:hover{background:#b91c1c}
.btn-green{background:var(--green)}.btn-green:hover{background:#15803d}
.btn-gray{background:#fff;border:1px solid var(--border);color:#374151}.btn-gray:hover{background:#f8fafc;border-color:#cbd5e1}
.btn-ghost{background:var(--blue-soft);color:var(--blue)}.btn-ghost:hover{background:#dde8ff}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:99px;font-size:11px;font-weight:700}
.b-pending{background:#fef3c7;color:#92400e}.b-paid{background:#dbeafe;color:#1e40af}.b-processing{background:#e0e7ff;color:#3730a3}
.b-shipped{background:#cffafe;color:#155e75}.b-completed{background:#dcfce7;color:#166534}.b-cancelled{background:#fee2e2;color:#991b1b}
.ps-unpaid,.ps-pending{background:#fef3c7;color:#92400e}.ps-paid{background:#dcfce7;color:#166534}.ps-failed,.ps-expired{background:#fee2e2;color:#991b1b}.ps-refunded{background:#e0e7ff;color:#3730a3}
.chip{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:8px;font-size:11px;font-weight:700;background:#f1f5f9;color:#475569}
.chip.ok{background:#dcfce7;color:#166534}.chip.low{background:#fef3c7;color:#92400e}.chip.out{background:#fee2e2;color:#991b1b}

/* ── FORM ── */
.inp,select.inp,textarea.inp{border:1px solid var(--border);border-radius:10px;padding:10px 14px;font-size:13.5px;width:100%;outline:none;transition:all .15s;background:#fff}
.inp:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.frm-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.fld{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.fld label{font-size:12.5px;font-weight:600;color:#374151}
.fld.full{grid-column:1/-1}
.toolbar{display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
.toolbar form{display:flex;gap:8px;flex:1;max-width:420px}
.thumb{width:44px;height:44px;border-radius:9px;object-fit:cover;border:1px solid var(--border)}
.empty{padding:54px;text-align:center;color:var(--muted)}
.empty .ei{font-size:40px;margin-bottom:10px;opacity:.4}

/* ── PAGINATION ── */
.pag{padding:16px 22px;border-top:1px solid var(--border)}
.pagination{display:flex;gap:5px;align-items:center;flex-wrap:wrap;list-style:none}
.pagination li a,.pagination li span{display:flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border-radius:9px;font-size:13px;font-weight:600;border:1px solid var(--border);color:#475569;background:#fff;transition:all .15s}
.pagination li a:hover{border-color:var(--blue);color:var(--blue)}
.pagination li.active span{background:var(--blue);color:#fff;border-color:var(--blue)}
.pagination li.disabled span{opacity:.4;cursor:not-allowed}

#toast{position:fixed;bottom:24px;right:24px;background:#0f172a;color:#fff;padding:13px 22px;border-radius:11px;font-size:13px;font-weight:600;z-index:9999;opacity:0;transform:translateY(8px);transition:all .3s;pointer-events:none;box-shadow:var(--shadow-lg)}

@media(max-width:900px){
  .cards{grid-template-columns:repeat(2,1fr)}
  .frm-grid{grid-template-columns:1fr}
}
@media(max-width:640px){
  .sb{transform:translateX(-100%);transition:transform .25s ease;box-shadow:var(--shadow-lg)}
  .sb.open{transform:translateX(0)}
  .mn{margin-left:0}
  .sb-toggle{display:inline-block}
  .tb{padding:13px 16px}
  .ct{padding:16px}
  .cards{grid-template-columns:1fr;gap:12px}
  .tb-r .who span,.tb-r .viewsite span{display:none}
  .toolbar form{max-width:none}
  .ct [style*="grid-template-columns:1fr 340px"],.ct [style*="grid-template-columns: 1fr 340px"]{grid-template-columns:1fr !important}
}
</style>
@stack('styles')
</head>
<body>
@php
  $grouped = collect($adminMenus ?? [])->groupBy('group');
@endphp
<div class="adm">
  <div class="sb-overlay" id="sbOverlay" onclick="toggleSidebar()"></div>
  <aside class="sb" id="sidebar">
    <div class="sb-logo">
      @if(!empty($site['brand.logo']))
        <img src="{{ $site['brand.logo'] }}" alt="{{ $site['brand.name'] ?? 'NIVICO' }}" class="sb-logo-mark">
      @else
        <div class="mark">{{ strtoupper(substr($site['brand.name'] ?? 'NV', 0, 2)) }}</div>
      @endif
      <div class="txt">{{ $site['brand.name'] ?? 'NIVICO' }}<small>ADMIN PANEL</small></div>
    </div>
    <nav class="sb-nav">
      @foreach($grouped as $group => $items)
        <div class="sb-group">{{ $group }}</div>
        @foreach($items as $key => $m)
          @php $active = request()->routeIs(str_replace('.index','',$m['route']).'*'); @endphp
          <a href="{{ route($m['route']) }}" class="{{ $active ? 'on' : '' }}">
            <span class="ico"><i class="{{ $m['icon'] ?? 'fa-solid fa-circle' }}"></i></span>
            <span>{{ $m['label'] }}</span>
          </a>
        @endforeach
      @endforeach
    </nav>
    <div class="sb-foot">
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</button>
      </form>
    </div>
  </aside>

  <div class="mn">
    <div class="tb">
      <div style="display:flex;align-items:center">
        <button class="sb-toggle" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
        <h1>@yield('heading', 'Dashboard')</h1>
      </div>
      <div class="tb-r">
        <a class="viewsite" href="{{ route('home') }}" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Lihat Toko</span></a>
        <span class="who"><span class="av">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span>{{ auth()->user()->name }}</span></span>
      </div>
    </div>
    <div class="ct">
      @yield('content')
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
function toggleSidebar(){var s=document.getElementById('sidebar'),o=document.getElementById('sbOverlay');s.classList.toggle('open');o.classList.toggle('show');}
function toast(m){var t=document.getElementById('toast');t.textContent=m;t.style.opacity='1';t.style.transform='translateY(0)';clearTimeout(t._t);t._t=setTimeout(function(){t.style.opacity='0';t.style.transform='translateY(8px)';},2800);}
function confirmDelete(f){return confirm('Yakin ingin menghapus data ini?');}
@if(session('toast'))document.addEventListener('DOMContentLoaded',function(){toast(@json(session('toast')))});@endif
@if(session('error'))document.addEventListener('DOMContentLoaded',function(){toast(@json('✗ '.session('error')))});@endif
@if($errors->any())document.addEventListener('DOMContentLoaded',function(){toast(@json('✗ '.$errors->first()))});@endif
</script>
@stack('scripts')
</body>
</html>
