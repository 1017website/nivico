<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<title>@yield('title', $seo->title ?? 'NIVICO Electronic Mart')</title>
@if($seo ?? null)
  @if($seo->meta_description)<meta name="description" content="{{ $seo->meta_description }}">@endif
  @if($seo->meta_keywords)<meta name="keywords" content="{{ $seo->meta_keywords }}">@endif
  @if($seo->noindex)<meta name="robots" content="noindex,nofollow">@endif
  @if($seo->canonical_url)<link rel="canonical" href="{{ $seo->canonical_url }}">@endif
  <meta property="og:title" content="{{ $seo->og_title ?: $seo->title }}">
  @if($seo->og_description)<meta property="og:description" content="{{ $seo->og_description }}">@endif
  @if($seo->og_image)<meta property="og:image" content="{{ $seo->og_image }}">@endif
  <meta property="og:type" content="website">
@endif
@php
  $appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1';
  $responsiveCssVersion = file_exists(public_path('css/responsive.css')) ? filemtime(public_path('css/responsive.css')) : '1';
@endphp
<link rel="stylesheet" href="{{ asset('vendor/fonts/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ $appCssVersion }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ $responsiveCssVersion }}">
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
@stack('styles')
</head>
<body>

@include('partials.topbar')
@include('partials.header')
@include('partials.nav')

<main>
    @yield('content')
</main>

@include('partials.footer')
@include('partials.mobile-nav')

{{-- TOAST --}}
<div id="toast"></div>

<script src="{{ asset('js/app.js') }}"></script>

{{-- Flash message -> toast --}}
@if(session('toast'))
<script>document.addEventListener('DOMContentLoaded',()=>toast(@json(session('toast'))));</script>
@endif
@if(session('error'))
<script>document.addEventListener('DOMContentLoaded',()=>toast(@json('✗ '.session('error'))));</script>
@endif
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',()=>toast(@json('✗ '.$errors->first())));</script>
@endif

@stack('scripts')
</body>
</html>
