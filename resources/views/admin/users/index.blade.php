@extends('layouts.admin')
@section('title', 'Pengguna')
@section('heading', 'Pengguna')

@section('content')
<div class="toolbar">
  <form method="GET" action="{{ route('admin.users.index') }}">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email...">
    <button class="btn" type="submit">Cari</button>
  </form>
  <a class="btn btn-blue" href="{{ route('admin.users.create') }}" style="margin-left:auto">+ Tambah Pengguna</a>
</div>

<div class="panel">
  @if($users->isEmpty())
    <div class="empty">Belum ada pengguna.</div>
  @else
  <table>
    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Bergabung</th><th></th></tr></thead>
    <tbody>
      @foreach($users as $u)
        <tr>
          <td style="font-weight:600">{{ $u->name }}</td>
          <td style="color:var(--muted)">{{ $u->email }}</td>
          <td>@if($u->role)<span class="badge b-paid">{{ $u->role->name }}</span>@else<span class="badge b-pending">{{ ucfirst($u->role ?? 'customer') }}</span>@endif</td>
          <td><span class="badge {{ $u->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td style="color:var(--muted)">{{ $u->created_at->format('d M Y') }}</td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm btn-blue" href="{{ route('admin.users.edit', $u) }}">Edit</a>
            @if($u->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit">Hapus</button>
            </form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div class="pag">{{ $users->links() }}</div>
  @endif
</div>
@endsection
