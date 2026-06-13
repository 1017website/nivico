@extends('layouts.admin')
@section('title', 'Pesan Masuk')
@section('heading', 'Pesan Masuk')

@section('content')
<div class="panel">
  <div class="panel-hd"><h2>Kotak Masuk</h2></div>
  @if($messages->isEmpty())
    <div class="empty">Belum ada pesan masuk.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>Pengirim</th><th>Topik</th><th>Pesan</th><th>Tanggal</th><th></th></tr></thead>
    <tbody>
      @foreach($messages as $m)
        <tr style="{{ $m->is_read ? '' : 'background:#f0f7ff' }}">
          <td>
            <div style="font-weight:600">{{ $m->name }} @unless($m->is_read)<span class="badge b-paid" style="margin-left:4px">Baru</span>@endunless</div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $m->email }}@if($m->phone) • {{ $m->phone }}@endif</div>
          </td>
          <td>{{ $m->topic ?: '—' }}</td>
          <td style="max-width:360px;color:#374151">{{ \Illuminate\Support\Str::limit($m->message, 120) }}</td>
          <td style="color:var(--muted);white-space:nowrap">{{ $m->created_at->format('d M Y H:i') }}</td>
          <td style="white-space:nowrap">
            @unless($m->is_read)
              <form method="POST" action="{{ route('admin.messages.read', $m) }}" style="display:inline">@csrf @method('PATCH')
                <button class="btn btn-sm btn-gray" type="submit">Tandai Dibaca</button>
              </form>
            @endunless
            <form method="POST" action="{{ route('admin.messages.destroy', $m) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="pag">{{ $messages->links() }}</div>
  @endif
</div>
@endsection
