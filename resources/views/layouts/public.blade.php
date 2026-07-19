<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-100 dark:bg-backgrounddark min-h-screen flex flex-col">
    {{-- Layout público (home, planos, checkout — tasks 4/12). Menu de
         topo deliberadamente SEM "Seja parceiro" — mantém o público de
         lava-rápido em segundo plano (task-12, seção 4). --}}
    <header class="bg-backgroundsecond">
        <div class="max-w-6xl mx-auto flex items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="40" height="40" class="w-10 h-10">
                <span class="text-gray-200 font-semibold">Celke Wash Club</span>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm text-gray-400">
                <a href="{{ url('/#como-funciona') }}" class="hover:text-gray-200">Como funciona</a>
                <a href="{{ Route::has('plans.index') ? route('plans.index') : url('/#planos') }}" class="hover:text-gray-200">Planos</a>
                <a href="{{ url('/#lava-rapidos') }}" class="hover:text-gray-200">Lava-rápidos</a>
                <a href="{{ url('/#faq') }}" class="hover:text-gray-200">FAQ</a>
            </nav>
            <div class="flex items-center gap-4 text-sm text-gray-400">
                {{-- Visível inclusive deslogado — a home é a página mais
                     vista/gravada em vídeo (task-18, seção 3). --}}
                <x-notification-bell />
                <x-theme-toggle class="text-gray-400 hover:text-gray-200 md:text-gray-400 md:hover:text-gray-200" />
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hover:text-gray-200">Entrar</a>
                @endif
                @if (Route::has('plans.index'))
                    <a href="{{ route('plans.index') }}" class="btn-primary">Assinar agora</a>
                @endif
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-backgroundsecond text-gray-400 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-2 sm:grid-cols-4 gap-6">
            <div class="col-span-2 sm:col-span-1">
                <div class="flex items-center gap-2 mb-2">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="28" height="28" class="w-7 h-7">
                    <span class="text-gray-200 font-semibold">Celke Wash Club</span>
                </div>
                <p class="text-xs">© {{ date('Y') }} Celke Wash Club — Celke Cursos</p>
            </div>

            <div>
                <p class="text-gray-200 font-medium mb-2">Institucional</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('about') }}" class="hover:text-gray-200">Sobre</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-gray-200">Contato</a></li>
                </ul>
            </div>

            <div>
                <p class="text-gray-200 font-medium mb-2">Legal</p>
                <ul class="space-y-1">
                    <li><a href="{{ route('terms') }}" class="hover:text-gray-200">Termos de uso</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-gray-200">Privacidade</a></li>
                </ul>
            </div>

            <div>
                <p class="text-gray-200 font-medium mb-2">Parceria</p>
                {{-- Discreto de propósito, sem CTA chamativo (task-12, seção 3). --}}
                <p class="text-xs">
                    É dono de lava-rápido?
                    <a href="{{ route('partners.register') }}" class="hover:text-gray-200 underline">Cadastre-se aqui</a>.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
