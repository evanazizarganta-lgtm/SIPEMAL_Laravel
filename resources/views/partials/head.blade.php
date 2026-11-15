<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#2563EB">
<title>@yield('title', 'SIPEMAL')</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
{{-- Font opsional: bila offline, otomatis memakai font sistem (lihat --font di CSS). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="{{ asset('css/sipemal.css') }}?v={{ @filemtime(public_path('css/sipemal.css')) }}">
@stack('head')
