@props(['name' => '', 'size' => 16, 'img' => ''])
@php
  $s = (int) $size;
  $img = trim($img);
  // Pakai Font Awesome 6 Brands (dimuat via CDN/local di layout).
  $map = [
    'instagram' => 'fa-brands fa-instagram',
    'whatsapp'  => 'fa-brands fa-whatsapp',
    'facebook'  => 'fa-brands fa-facebook-f',
    'tiktok'    => 'fa-brands fa-tiktok',
    'youtube'   => 'fa-brands fa-youtube',
    'twitter'   => 'fa-brands fa-x-twitter',
    // Tokopedia & Shopee tidak ada di FA Brands -> pakai SVG brand inline.
  ];
  $key = strtolower($name);

  $svgBrands = [
    'tokopedia' => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2 3.5 6v8c0 4.4 3.6 7.2 8.5 8 4.9-.8 8.5-3.6 8.5-8V6L12 2Zm0 2.3 6.5 3v6.7c0 3.2-2.5 5.3-6.5 6-4-.7-6.5-2.8-6.5-6V7.3l6.5-3Zm-2.6 4.4v6.6h1.9v-2.3h.7l1.3 2.3h2.2l-1.6-2.7c.8-.4 1.3-1.1 1.3-2 0-1.3-1-2.2-2.6-2.2H9.4Zm1.9 1.5h.8c.6 0 .9.3.9.8s-.3.8-.9.8h-.8v-1.6Z"/></svg>',
    'shopee'    => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1.5c-2.5 0-4.5 2-4.5 4.5v.6H3.3a1 1 0 0 0-1 1.08l.84 12.1a2.5 2.5 0 0 0 2.5 2.32h12.7a2.5 2.5 0 0 0 2.5-2.32l.84-12.1a1 1 0 0 0-1-1.08h-4.2v-.6c0-2.5-2-4.5-4.5-4.5Zm0 1.8c1.5 0 2.7 1.2 2.7 2.7v.6H9.3v-.6c0-1.5 1.2-2.7 2.7-2.7Zm-.3 6.4c1.9 0 3.1.9 3.1 2.4 0 1.3-1 2-2.3 2.4-1.2.3-1.6.6-1.6 1.1 0 .5.5.8 1.2.8.7 0 1.4-.2 2-.6l.5 1.4c-.7.4-1.6.6-2.5.6-1.9 0-3.1-1-3.1-2.4 0-1.4 1.1-2 2.4-2.4 1.1-.3 1.5-.5 1.5-1 0-.5-.5-.8-1.2-.8-.7 0-1.5.3-2.1.7l-.5-1.4c.7-.5 1.7-.8 2.7-.8Z"/></svg>',
  ];
@endphp
@if($img !== '')
  {{-- Ikon custom yang di-upload admin lewat CMS --}}
  <img src="{{ $img }}" alt="{{ ucfirst($name) }}" width="{{ $s }}" height="{{ $s }}" style="width:{{ $s }}px;height:{{ $s }}px;object-fit:contain;display:inline-block;vertical-align:middle" loading="lazy">
@elseif(isset($map[$key]))
  <i class="{{ $map[$key] }}" style="font-size:{{ $s }}px;line-height:1" aria-hidden="true"></i>
@elseif(isset($svgBrands[$key]))
  {!! $svgBrands[$key] !!}
@endif
