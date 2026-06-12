@extends('layouts.app')
@section('title', 'Masuk — NIVICO Electronic Mart')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><div class="logo-ring"><span class="r1">NIVICO</span><span class="r2">Electronic Mart</span></div></div>
    <h1 class="auth-title">Masuk ke Akun</h1>
    <p class="auth-sub">Selamat datang kembali di NIVICO Electronic Mart</p>

    <div class="social-login">
      <button type="button" class="btn-social" onclick="toast('Login sosial belum tersedia')">🌐 Masuk dengan Google</button>
      <button type="button" class="btn-social" onclick="toast('Login sosial belum tersedia')">📘 Masuk dengan Facebook</button>
    </div>
    <div class="auth-divider">atau masuk dengan email</div>

    <form method="POST" action="{{ route('login.attempt') }}">
      @csrf
      <div class="auth-fg"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required autofocus></div>
      <div class="auth-fg"><label>Password</label><div class="pw-wrap"><input type="password" name="password" placeholder="Masukkan password" id="pw1" required><button type="button" class="pw-toggle" onclick="togglePw('pw1')">👁</button></div></div>
      <div class="auth-opts"><label><input type="checkbox" name="remember" value="1"> Ingat saya</label><a href="#" onclick="toast('Hubungi admin untuk reset password');return false">Lupa password?</a></div>
      <button class="btn-auth" type="submit">Masuk</button>
    </form>

    <div class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></div>
  </div>
</div>
@endsection
