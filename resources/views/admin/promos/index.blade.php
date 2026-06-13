@extends('layouts.admin')
@section('title', 'Promo')
@section('heading', 'Promo')

@section('content')
<div class="toolbar">
  <a class="btn btn-blue" href="{{ route('admin.promos.create') }}" style="margin-left:auto"><i class="fa-solid fa-plus"></i> Tambah Promo</a>
</div>

<div class="panel">
  @if($promos->isEmpty())
    <div class="empty">Belum ada promo.</div>
  @else
  <div class="table-wrap"><table>
    <thead><tr><th>Kode</th><th>Judul</th><th>Tipe</th><th>Nilai</th><th>Min. Belanja</th><th>Berlaku s/d</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach($promos as $p)
        <tr>
          <td><span class="badge b-paid">{{ $p->code }}</span></td>
          <td>{{ $p->title }}@if($p->badge)<div style="font-size:11px;color:var(--muted)">{{ $p->badge }}</div>@endif</td>
          <td>{{ ['fixed'=>'Potongan','percent'=>'Persen','free_shipping'=>'Gratis Ongkir'][$p->type] ?? $p->type }}</td>
          <td>@if($p->type==='percent'){{ $p->value }}%@elseif($p->type==='fixed')Rp{{ number_format($p->value,0,',','.') }}@else—@endif</td>
          <td style="color:var(--muted)">Rp{{ number_format($p->min_purchase,0,',','.') }}</td>
          <td style="color:var(--muted)">{{ $p->expires_at ? $p->expires_at->format('d M Y') : '∞' }}</td>
          <td><span class="badge {{ $p->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td style="white-space:nowrap">
            <a class="btn btn-sm btn-blue" href="{{ route('admin.promos.edit', $p) }}"><i class="fa-solid fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('admin.promos.destroy', $p) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
              <button class="btn btn-sm btn-red" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
  <div class="pag">{{ $promos->links() }}</div>
  @endif
</div>
@endsection
