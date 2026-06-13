@extends('layouts.admin')
@section('title', $role->exists ? 'Edit Role' : 'Tambah Role')
@section('heading', $role->exists ? 'Edit Role' : 'Tambah Role')

@section('content')
<div class="panel" style="max-width:760px">
  <div class="panel-hd"><h2>{{ $role->exists ? 'Edit: '.$role->name : 'Role Baru' }}</h2><a class="btn btn-sm btn-gray" href="{{ route('admin.roles.index') }}">← Kembali</a></div>
  <div style="padding:24px">
    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
      @csrf
      @if($role->exists) @method('PUT') @endif
      <div class="frm-grid">
        <div class="fld"><label>Nama Role</label><input class="inp" type="text" name="name" value="{{ old('name', $role->name) }}" required></div>
        <div class="fld"><label>Deskripsi</label><input class="inp" type="text" name="description" value="{{ old('description', $role->description) }}"></div>
      </div>

      @if($role->is_locked)
        <div style="background:#e0e7ff;color:#3730a3;border-radius:8px;padding:14px;font-size:13px">Role ini terkunci dan otomatis memiliki <strong>semua hak akses</strong>.</div>
      @else
        <label style="font-size:13px;font-weight:700;display:block;margin:8px 0 12px">Hak Akses Menu</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
          @foreach($permissions as $group => $perms)
            <div style="border:1px solid var(--border);border-radius:8px;padding:14px">
              <div style="font-weight:700;font-size:12.5px;color:var(--navy);margin-bottom:8px">{{ $group }}</div>
              @foreach($perms as $p)
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:6px;cursor:pointer">
                  <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked(in_array($p->id, old('permissions', $assigned)))>
                  {{ $p->name }}
                </label>
              @endforeach
            </div>
          @endforeach
        </div>
      @endif

      <div style="display:flex;gap:10px;margin-top:18px">
        <button class="btn btn-blue" type="submit">💾 Simpan</button>
        <a class="btn btn-gray" href="{{ route('admin.roles.index') }}">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
