@if ($paginator->hasPages())
<nav class="nv-pg" role="navigation" aria-label="Pagination">
  {{-- Sebelumnya --}}
  @if ($paginator->onFirstPage())
    <span class="nv-pg-btn disabled" aria-disabled="true">‹</span>
  @else
    <a class="nv-pg-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">‹</a>
  @endif

  {{-- Nomor halaman --}}
  @foreach ($elements as $element)
    @if (is_string($element))
      <span class="nv-pg-dots">{{ $element }}</span>
    @endif
    @if (is_array($element))
      @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
          <span class="nv-pg-btn active" aria-current="page">{{ $page }}</span>
        @else
          <a class="nv-pg-btn" href="{{ $url }}">{{ $page }}</a>
        @endif
      @endforeach
    @endif
  @endforeach

  {{-- Berikutnya --}}
  @if ($paginator->hasMorePages())
    <a class="nv-pg-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">›</a>
  @else
    <span class="nv-pg-btn disabled" aria-disabled="true">›</span>
  @endif
</nav>
@endif
