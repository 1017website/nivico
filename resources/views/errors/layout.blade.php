<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <meta name="theme-color" content="#172a88">
  <title>@yield('title') — NIVICO Electronic Mart</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <style>
    :root{--navy:#172a88;--navy-dark:#101d61;--blue:#2563eb;--text:#111827;--muted:#64748b;--line:#e2e8f0;--surface:#fff;--bg:#f4f6fb}
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{min-height:100%}
    body{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
    .error-page{min-height:100vh;display:grid;grid-template-rows:auto 1fr auto;padding:0 24px;background:
      radial-gradient(circle at 10% 12%,rgba(37,99,235,.08),transparent 28%),
      radial-gradient(circle at 90% 88%,rgba(23,42,136,.07),transparent 30%),
      var(--bg)}
    .error-header,.error-footer{width:100%;max-width:1120px;margin:0 auto}
    .error-header{height:88px;display:flex;align-items:center;border-bottom:1px solid rgba(226,232,240,.85)}
    .brand{display:inline-flex;align-items:center;gap:11px;text-decoration:none}
    .brand-mark{display:block;width:44px;height:44px;border-radius:50%;box-shadow:0 5px 15px rgba(15,23,42,.12)}
    .brand-copy{display:grid;gap:1px}
    .brand-copy strong{color:var(--navy-dark);font-size:20px;line-height:1;font-weight:850;letter-spacing:.01em}
    .brand-copy small{color:#64748b;font-size:10.5px;letter-spacing:.03em}
    .error-main{width:100%;max-width:760px;margin:auto;text-align:center;padding:56px 0 72px}
    .status-mark{width:88px;height:88px;margin:0 auto 26px;border:1px solid #cbd5e1;border-radius:24px;background:rgba(255,255,255,.82);box-shadow:0 16px 42px rgba(15,23,42,.08);display:grid;place-items:center;color:var(--navy);font-size:22px;font-weight:800;letter-spacing:.04em;transform:rotate(-3deg)}
    .status-mark span{transform:rotate(3deg)}
    .eyebrow{color:var(--blue);font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;margin-bottom:12px}
    h1{max-width:650px;margin:0 auto;font-size:clamp(32px,5vw,52px);line-height:1.1;letter-spacing:-.035em;color:var(--navy-dark)}
    .message{max-width:590px;margin:18px auto 0;color:var(--muted);font-size:16px;line-height:1.75}
    .actions{display:flex;justify-content:center;align-items:center;gap:10px;flex-wrap:wrap;margin-top:30px}
    .btn{min-height:46px;border-radius:9px;padding:12px 20px;font-size:14px;font-weight:750;text-decoration:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:transform .15s,background .15s,border-color .15s}
    .btn:hover{transform:translateY(-1px)}
    .btn-primary{background:var(--navy);border:1px solid var(--navy);color:#fff}
    .btn-primary:hover{background:var(--navy-dark)}
    .btn-secondary{background:#fff;border:1px solid #cbd5e1;color:#334155}
    .btn-secondary:hover{border-color:#94a3b8;background:#f8fafc}
    .support{margin-top:34px;color:#94a3b8;font-size:12px}
    .support a{color:#475569;font-weight:700;text-decoration:none}
    .support a:hover{text-decoration:underline}
    .error-footer{min-height:72px;border-top:1px solid rgba(226,232,240,.85);display:flex;align-items:center;justify-content:space-between;gap:16px;color:#94a3b8;font-size:11.5px}
    .error-footer strong{color:#64748b;font-weight:700}
    @media(max-width:600px){
      .error-page{padding:0 18px}
      .error-header{height:76px}
      .brand-mark{width:40px;height:40px}
      .brand-copy strong{font-size:18px}
      .error-main{padding:42px 0 56px}
      .status-mark{width:76px;height:76px;border-radius:20px;margin-bottom:22px}
      .message{font-size:14.5px}
      .actions{display:grid;grid-template-columns:1fr;margin-top:26px}
      .btn{width:100%}
      .error-footer{padding:18px 0;display:block;text-align:center;line-height:1.7}
    }
  </style>
</head>
<body>
  <div class="error-page">
    <header class="error-header">
      <a class="brand" href="{{ url('/') }}" aria-label="NIVICO Electronic Mart">
        <img class="brand-mark" src="{{ asset('favicon.png') }}" alt="" onerror="this.hidden=true">
        <span class="brand-copy"><strong>NIVICO</strong><small>Electronic Mart</small></span>
      </a>
    </header>

    <main class="error-main">
      <div class="status-mark" aria-hidden="true"><span>@yield('code')</span></div>
      <div class="eyebrow">NIVICO Electronic Mart</div>
      <h1>@yield('heading')</h1>
      <p class="message">@yield('message')</p>
      <div class="actions">
        <a class="btn btn-primary" href="{{ url('/') }}">Kembali ke Beranda</a>
        <button class="btn btn-secondary" type="button" onclick="if(history.length>1){history.back()}else{location.href='{{ url('/') }}'}">Kembali ke Halaman Sebelumnya</button>
      </div>
      <p class="support">Masih membutuhkan bantuan? <a href="mailto:info@nivico.id">Hubungi layanan pelanggan</a></p>
    </main>

    <footer class="error-footer">
      <span>© {{ date('Y') }} <strong>NIVICO Electronic Mart</strong></span>
      <span>Belanja elektronik dengan aman dan nyaman.</span>
    </footer>
  </div>
</body>
</html>
