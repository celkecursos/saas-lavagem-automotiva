{{-- Head compartilhado pelos três layouts (task-14, seção 1). --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
{{-- Favicon copiado na task-1 (ver task-6, seção 3.1). --}}
<link rel="icon" href="{{ asset('favicon.ico') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
