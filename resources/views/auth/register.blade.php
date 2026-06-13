@extends('layouts.app')
@section('title', 'Daftar — NIVICO Electronic Mart')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><div class="logo-ring"><span class="r1">NIVICO</span><span class="r2">Electronic Mart</span></div></div>
    <h1 class="auth-title">Buat Akun Baru</h1>
    <p class="auth-sub">Bergabung dan nikmati kemudahan belanja elektronik</p>

    <form method="POST" action="{{ route('register.store') }}">
      @csrf
      <div class="fr" style="gap:12px;margin-bottom:0">
        <div class="auth-fg" style="margin-bottom:0"><label>Nama Depan</label><input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Nama depan" required></div>
        <div class="auth-fg" style="margin-bottom:0"><label>Nama Belakang</label><input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Nama belakang"></div>
      </div>
      <div style="height:12px"></div>
      <div class="auth-fg"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="email@contoh.com" required></div>
      <div class="auth-fg"><label>No. Telepon</label><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx"></div>
      <div class="auth-fg"><label>Password</label><div class="pw-wrap"><input type="password" name="password" placeholder="Min. 8 karakter" id="pw2" required><button type="button" class="pw-toggle" onclick="togglePw('pw2')">👁</button></div></div>
      <div class="auth-fg"><label>Konfirmasi Password</label><div class="pw-wrap"><input type="password" name="password_confirmation" placeholder="Ulangi password" id="pw3" required><button type="button" class="pw-toggle" onclick="togglePw('pw3')">👁</button></div></div>
      <div class="auth-opts" style="justify-content:flex-start"><label><input type="checkbox" name="agree" value="1" required> Saya setuju dengan <a style="color:var(--blue)">Syarat &amp; Ketentuan</a> dan <a style="color:var(--blue)">Kebijakan Privasi</a></label></div>
      <button class="btn-auth" type="submit">Buat Akun</button>
    </form>

    <div class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></div>
  </div>
</div>
@endsection
