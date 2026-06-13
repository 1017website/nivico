@extends('layouts.admin')
@section('title', 'Log Aktivitas')
@section('heading', 'Log Aktivitas')

@section('content')
<div class="toolbar">
  <form method="GET" action="{{ route('admin.activity.index') }}">
    <input class="inp" type="text" name="q" value="{{ request('q') }}" placeholder="Cari user / deskripsi...">
    <select class="inp" name="action" onchange="this.form.submit()" style="max-width:150px">
      <option value="">Semua Aksi</option>
      @foreach(['created'=>'Tambah','updated'=>'Ubah','deleted'=>'Hapus','login'=>'Masuk','logout'=>'Keluar'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('action')===$k)>{{ $v }}</option>
      @endforeach
    </select>
    <button class="btn" type="submit">Filter</button>
  </form>
</div>
<div class="panel">
  @if($logs->isEmpty())
    <div class="empty">Belum ada aktivitas tercatat.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Keterangan</th><th>IP</th></tr></thead>
    <tbody>
      @foreach($logs as $log)
        <tr>
          <td style="color:var(--muted);white-space:nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
          <td style="font-weight:600">{{ $log->user_name ?: 'Sistem' }}</td>
          <td><span class="badge {{ $log->actionColor() }}">{{ $log->actionLabel() }}</span></td>
          <td>{{ $log->description }}@if(!empty($log->properties['fields']))<div style="font-size:11px;color:var(--muted)">Field: {{ implode(', ', $log->properties['fields']) }}</div>@endif</td>
          <td style="color:var(--muted);font-size:12px">{{ $log->ip_address }}</td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="pag">{{ $logs->links() }}</div>
  @endif
</div>
@endsection
