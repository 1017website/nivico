@extends('layouts.admin')
@section('title', 'Detail Pesan')
@section('heading', 'Detail Pesan Masuk')

@section('content')
<div style="margin-bottom:18px"><a class="btn btn-sm btn-gray" href="{{ route('admin.messages.index') }}">&larr; Kembali ke kotak masuk</a></div>

<div class="panel" style="max-width:900px">
  <div class="panel-hd">
    <div>
      <h2>{{ $message->topic ?: 'Pesan tanpa topik' }}</h2>
      <div class="sub">Diterima {{ $message->created_at->format('d M Y H:i') }}</div>
    </div>
    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirmDelete()">
      @csrf @method('DELETE')
      <button class="btn btn-sm btn-red" type="submit"><i class="fa-solid fa-trash"></i> Hapus</button>
    </form>
  </div>
  <div style="padding:22px">
    <div style="display:grid;grid-template-columns:140px 1fr;gap:8px 16px;font-size:13.5px;margin-bottom:22px">
      <strong>Pengirim</strong><span>{{ $message->name }}</span>
      <strong>Email</strong><span><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></span>
      <strong>Telepon</strong><span>{{ $message->phone ?: '—' }}</span>
    </div>
    <div style="font-size:12px;font-weight:700;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Isi Pesan</div>
    <div style="white-space:pre-wrap;line-height:1.7;background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:18px">{{ $message->message }}</div>
  </div>
</div>
@endsection
