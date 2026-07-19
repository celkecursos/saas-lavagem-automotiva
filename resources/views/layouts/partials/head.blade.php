{{-- Head compartilhado pelos três layouts (task-14, seção 1). --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
{{-- SEO/Open Graph (task-12, seção 5) — cada página pública define o
     próprio @section('meta_description'/'og_image'); sem isso, cai no
     texto/imagem padrão voltados pro consumidor final. --}}
<meta name="description" content="@yield('meta_description', 'Assine o Celke Wash Club e lave seu carro quando quiser em qualquer lava-rápido parceiro, por uma mensalidade só.')">
<meta property="og:title" content="@yield('title', config('app.name'))">
<meta property="og:description" content="@yield('meta_description', 'Assine o Celke Wash Club e lave seu carro quando quiser em qualquer lava-rápido parceiro, por uma mensalidade só.')">
<meta property="og:image" content="@yield('og_image', asset('images/logo.png'))">
<meta property="og:type" content="website">
{{-- Favicon copiado na task-1 (ver task-6, seção 3.1). --}}
<link rel="icon" href="{{ asset('favicon.ico') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
