<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
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
