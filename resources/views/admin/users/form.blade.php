@extends('layouts.admin')
@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="panel" style="max-width:680px">
  <div class="panel-hd"><h2>{{ $user->exists ? 'Edit: '.$user->name : 'Pengguna Baru' }}</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.users.index') }}">← Kembali</a></div>
  <div style="padding:24px">
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
      @csrf
      @if($user->exists) @method('PUT') @endif
      <div class="frm-grid">
        <div class="fld"><label>Nama Depan</label><input class="inp" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required></div>
        <div class="fld"><label>Nama Belakang</label><input class="inp" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"></div>
        <div class="fld"><label>Email</label><input class="inp" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
        <div class="fld"><label>No. Telepon</label><input class="inp" type="text" name="phone" value="{{ old('phone', $user->phone) }}"></div>
        <div class="fld"><label>Role / Hak Akses</label>
          <select class="inp" name="role_id" required>
            <option value="">Pilih role</option>
            @foreach($roles as $r)
              <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id)==$r->id)>{{ $r->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="fld"><label>Status</label>
          <select class="inp" name="is_active">
            <option value="1" @selected(old('is_active', $user->exists ? $user->is_active : true))>Aktif</option>
            <option value="0" @selected(old('is_active', $user->exists ? $user->is_active : true)==false)>Nonaktif</option>
          </select>
        </div>
        <div class="fld"><label>Password {{ $user->exists ? '(kosongkan bila tidak diubah)' : '' }}</label><input class="inp" type="password" name="password" {{ $user->exists ? '' : 'required' }}></div>
        <div class="fld"><label>Konfirmasi Password</label><input class="inp" type="password" name="password_confirmation"></div>
      </div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-blue" type="submit">💾 Simpan</button>
        <a class="btn btn-gray" href="{{ route('admin.users.index') }}">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
