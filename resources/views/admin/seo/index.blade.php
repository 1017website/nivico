@extends('layouts.admin')
@section('title', 'Pengaturan SEO')
@section('heading', 'Pengaturan SEO')

@section('content')
<div class="panel">
  <div class="panel-hd"><h2>Meta SEO per Halaman</h2></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Halaman</th><th>Title</th><th>Meta Description</th><th>Index</th><th></th></tr></thead>
    <tbody>
      @foreach($pages as $key => $label)
        @php $s = $settings[$key] ?? null; @endphp
        <tr>
          <td style="font-weight:600">{{ $label }}</td>
          <td style="color:var(--muted)">{{ $s->title ?? '—' }}</td>
          <td style="color:var(--muted);max-width:320px">{{ \Illuminate\Support\Str::limit($s->meta_description ?? '—', 80) }}</td>
          <td>@if($s && $s->noindex)<span class="badge b-cancelled">noindex</span>@else<span class="badge b-completed">index</span>@endif</td>
          <td><a class="btn btn-sm btn-blue" href="{{ route('admin.seo.edit', $key) }}">Edit</a></td>
        </tr>
      @endforeach
    </tbody>
  </table></div>
</div>
@endsection
