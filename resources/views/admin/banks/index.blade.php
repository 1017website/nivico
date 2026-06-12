@extends('layouts.admin')
@section('title', 'Rekening Bank')
@section('heading', 'Rekening Bank')

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
  <div class="panel">
    <div class="panel-hd"><h2>Daftar Rekening</h2></div>
    @if($banks->isEmpty())
      <div class="empty">Belum ada rekening bank.</div>
    @else
    <table>
      <thead><tr><th>Bank</th><th>No. Rekening</th><th>Atas Nama</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach($banks as $b)
          <tr>
            <td style="font-weight:600">{{ $b->bank_name }}</td>
            <td>{{ $b->account_number }}</td>
            <td>{{ $b->account_holder }}</td>
            <td><span class="badge {{ $b->is_active ? 'b-completed' : 'b-cancelled' }}">{{ $b->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td style="white-space:nowrap">
              <form method="POST" action="{{ route('admin.banks.update', $b) }}" style="display:inline-flex;gap:6px;align-items:center">@csrf @method('PUT')
                <input type="hidden" name="bank_name" value="{{ $b->bank_name }}">
                <input type="hidden" name="account_number" value="{{ $b->account_number }}">
                <input type="hidden" name="account_holder" value="{{ $b->account_holder }}">
                <input type="hidden" name="is_active" value="{{ $b->is_active ? 0 : 1 }}">
                <button class="btn btn-sm btn-gray" type="submit">{{ $b->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              </form>
              <form method="POST" action="{{ route('admin.banks.destroy', $b) }}" style="display:inline" onsubmit="return confirmDelete()">@csrf @method('DELETE')
                <button class="btn btn-sm btn-red" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

  <div class="panel">
    <div class="panel-hd"><h2>Tambah Rekening</h2></div>
    <div style="padding:20px">
      <form method="POST" action="{{ route('admin.banks.store') }}">@csrf
        <div class="fld"><label>Nama Bank</label><input class="inp" type="text" name="bank_name" placeholder="BCA / Mandiri / BNI" required></div>
        <div class="fld"><label>No. Rekening</label><input class="inp" type="text" name="account_number" required></div>
        <div class="fld"><label>Atas Nama</label><input class="inp" type="text" name="account_holder" required></div>
        <button class="btn btn-blue" type="submit" style="width:100%">+ Tambah Rekening</button>
      </form>
    </div>
  </div>
</div>
@endsection
