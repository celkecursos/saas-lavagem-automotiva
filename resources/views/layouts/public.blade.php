<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-100 dark:bg-backgrounddark min-h-screen flex flex-col">
    {{-- Layout público (home, planos, checkout — tasks 4/12). O site
         institucional completo detalha este cabeçalho na task-12. --}}
    <header class="bg-backgroundsecond">
        <div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-10 h-10">
                <span class="text-gray-200 font-semibold">Celke Wash Club</span>
            </a>
            <nav class="flex items-center gap-4 text-sm text-gray-400">
                {{-- Visível inclusive deslogado — a home é a página mais
                     vista/gravada em vídeo (task-18, seção 3). --}}
                <x-notification-bell />
                <x-theme-toggle class="text-gray-400 hover:text-gray-200 md:text-gray-400 md:hover:text-gray-200" />
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-gray-200">Entrar</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-backgroundsecond text-gray-400 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-6">
            © {{ date('Y') }} Celke Wash Club — Celke Cursos
        </div>
    </footer>
</body>
</html>
