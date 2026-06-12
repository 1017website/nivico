<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'NIVICO Electronic Mart')</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
