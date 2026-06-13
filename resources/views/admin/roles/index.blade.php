@extends('layouts.admin')
@section('title', 'Hak Akses (Roles)')
@section('heading', 'Hak Akses')

@section('content')
<div class="toolbar">
  <a class="btn btn-blue" href="{{ route('admin.roles.create') }}" style="margin-left:auto"><i class="fa-solid fa-plus"></i> Tambah Role</a>
</div>
<div class="panel">
  @if($roles->isEmpty())
    <div class="empty">Belum ada role.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>Nama Role</th><th>Deskripsi</th><th>Pengguna</th><th>Permission</th><th></th></tr></thead>
    <tbody>
      @foreach($roles as $r)
        <tr>
          <td style="font-weight:600">{{ $r->name }} @if($r->is_locked)<span class="badge b-processing" style="margin-left:4px">Inti</span>@endif</td>
          <td style="color:var(--muted)">{{ $r->description ?: '—' }}</td>
          <td>{{ $r->users_count }}</td>
          <td>{{ $r->is_locked ? 'Semua (full)' : $r->permissions_count }}</td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm btn-blue" href="{{ route('admin.roles.edit', $r) }}"><i class="fa-solid fa-pen"></i> Edit</a>
            @unless($r->is_locked)
            <form method="POST" action="{{ route('admin.roles.destroy', $r) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
            </form>
            @endunless
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  @endif
</div>
@endsection
