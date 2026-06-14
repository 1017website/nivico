@extends('layouts.app')
@section('title', 'Masuk — NIVICO Electronic Mart')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><div class="logo-ring"><span class="r1">{{ $site['brand.name'] ?? 'NIVICO' }}</span><span class="r2">{{ $site['brand.tagline'] ?? 'Electronic Mart' }}</span></div></div>
    <h1 class="auth-title">Masuk ke Akun</h1>
    <p class="auth-sub">Selamat datang kembali di {{ $site['brand.name'] ?? 'NIVICO' }} {{ $site['brand.tagline'] ?? 'Electronic Mart' }}</p>

    <form method="POST" action="{{ route('login.attempt') }}">
      @csrf
      <div class="auth-fg"><label>Email</label><input type="email" name="email" id="loginEmail" value="{{ old('email') }}" placeholder="email@contoh.com" required autofocus></div>
      <div class="auth-fg"><label>Password</label><div class="pw-wrap"><input type="password" name="password" id="pw1" placeholder="Masukkan password" required><button type="button" class="pw-toggle" onclick="togglePw('pw1')">👁</button></div></div>
      <div class="auth-opts"><label><input type="checkbox" name="remember" value="1"> Ingat saya</label><a href="#" onclick="toast('Hubungi admin untuk reset password');return false">Lupa password?</a></div>
      <button class="btn-auth" type="submit">Masuk</button>
    </form>

    @unless(app()->environment('production'))
    @php
      $demoAccounts = [
        ['role' => 'Super Admin', 'email' => 'admin@nivico.id',    'pass' => 'password', 'desc' => 'Akses penuh',       'color' => '#2563eb'],
        ['role' => 'Staf Toko',   'email' => 'staf@nivico.id',     'pass' => 'password', 'desc' => 'Katalog & pesanan', 'color' => '#16a34a'],
        ['role' => 'Customer',    'email' => 'customer@nivico.id', 'pass' => 'password', 'desc' => 'Pembeli',           'color' => '#d97706'],
      ];
    @endphp
    <div class="demo-box">
      <div class="demo-hd"><span class="demo-dot"></span> Akun Demo <small>(klik untuk isi otomatis)</small></div>
      <div class="demo-list">
        @foreach($demoAccounts as $acc)
          <button type="button" class="demo-item" onclick="fillLogin('{{ $acc['email'] }}','{{ $acc['pass'] }}')">
            <span class="demo-badge" style="background:{{ $acc['color'] }}1a;color:{{ $acc['color'] }}">{{ $acc['role'] }}</span>
            <span class="demo-meta"><strong>{{ $acc['email'] }}</strong><span>{{ $acc['desc'] }}</span></span>
            <span class="demo-pass">{{ $acc['pass'] }}</span>
          </button>
        @endforeach
      </div>
    </div>
    @endunless

    <div class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></div>
  </div>
</div>

@push('styles')
<style>
.demo-box{margin-top:20px;border:1px dashed #cbd5e1;border-radius:14px;padding:14px;background:#f8fafc}
.demo-hd{font-size:12.5px;font-weight:700;color:#475569;display:flex;align-items:center;gap:7px;margin-bottom:11px}
.demo-hd small{font-weight:500;color:#94a3b8}
.demo-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px #22c55e33}
.demo-list{display:flex;flex-direction:column;gap:8px}
.demo-item{display:flex;align-items:center;gap:10px;width:100%;text-align:left;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:9px 11px;cursor:pointer;transition:all .15s;font-family:inherit}
.demo-item:hover{border-color:#2563eb;box-shadow:0 2px 8px rgba(37,99,235,.12);transform:translateY(-1px)}
.demo-badge{font-size:11px;font-weight:700;padding:4px 9px;border-radius:7px;white-space:nowrap;flex-shrink:0}
.demo-meta{display:flex;flex-direction:column;line-height:1.35;min-width:0;flex:1}
.demo-meta strong{font-size:12.5px;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.demo-meta span{font-size:11px;color:#94a3b8}
.demo-pass{font-size:11px;font-family:monospace;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px;flex-shrink:0}
@media(max-width:420px){.demo-pass{display:none}}
</style>
@endpush

@push('scripts')
<script>
function fillLogin(email, pass){
  var e = document.getElementById('loginEmail');
  var p = document.getElementById('pw1');
  if(e) e.value = email;
  if(p) p.value = pass;
  if(typeof toast === 'function') toast('Akun ' + email + ' terisi. Klik Masuk.');
}
</script>
@endpush
@endsection
