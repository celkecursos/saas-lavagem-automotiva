<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-100 dark:bg-backgrounddark min-h-screen flex flex-col">
    {{-- Layout público (home, planos, checkout — tasks 4/12). Menu de
         topo deliberadamente SEM "Seja parceiro" — mantém o público de
         lava-rápido em segundo plano (task-12, seção 4). --}}
    <header class="sticky top-0 z-40 border-b border-white/5 bg-backgroundsecond/95 backdrop-blur" x-data="{ mobile: false }">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4 px-4 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="36" height="36" class="w-9 h-9">
                <span class="text-gray-100 font-semibold tracking-tight">Celke Wash Club</span>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-sm text-gray-400">
                <a href="{{ url('/#como-funciona') }}" class="transition hover:text-white">Como funciona</a>
                <a href="{{ Route::has('plans.index') ? route('plans.index') : url('/#planos') }}" class="transition hover:text-white">Planos</a>
                <a href="{{ url('/#lava-rapidos') }}" class="transition hover:text-white">Lava-rápidos</a>
                <a href="{{ url('/#faq') }}" class="transition hover:text-white">FAQ</a>
            </nav>

            <div class="flex items-center gap-3 text-sm text-gray-400">
                {{-- Visível inclusive deslogado — a home é a página mais
                     vista/gravada em vídeo (task-18, seção 3). --}}
                <x-notification-bell />
                <x-theme-toggle class="text-gray-400 hover:text-white md:text-gray-400 md:hover:text-white" />
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hidden sm:inline transition hover:text-white">Entrar</a>
                @endif
                @if (Route::has('plans.index'))
                    <a href="{{ route('plans.index') }}"
                       class="hidden sm:inline-flex items-center justify-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-600">
                        Assinar agora
                    </a>
                @endif

                <button type="button" class="md:hidden text-gray-400 hover:text-white" @click="mobile = ! mobile" aria-label="Abrir menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="! mobile" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        <path x-show="mobile" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Menu mobile: os mesmos links do desktop, empilhados. --}}
        <nav x-show="mobile" x-cloak class="md:hidden border-t border-white/5 px-4 py-3 space-y-1 text-sm text-gray-400">
            <a href="{{ url('/#como-funciona') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white">Como funciona</a>
            <a href="{{ Route::has('plans.index') ? route('plans.index') : url('/#planos') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white">Planos</a>
            <a href="{{ url('/#lava-rapidos') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white">Lava-rápidos</a>
            <a href="{{ url('/#faq') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white">FAQ</a>
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white sm:hidden">Entrar</a>
            @endif
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-white/5 bg-backgroundsecond text-gray-400 text-sm">
        <div class="max-w-6xl mx-auto px-4 py-14 grid grid-cols-2 sm:grid-cols-4 gap-8">
            <div class="col-span-2 sm:col-span-1">
                <div class="flex items-center gap-2.5 mb-3">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="28" height="28" class="w-7 h-7">
                    <span class="text-gray-100 font-semibold tracking-tight">Celke Wash Club</span>
                </div>
                <p class="text-sm text-gray-500 max-w-xs">
                    Lavagens ilimitadas de conveniência, numa rede de lava-rápidos parceiros.
                </p>
            </div>

            <div>
                <p class="text-gray-100 font-medium mb-3">Institucional</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('about') }}" class="transition hover:text-white">Sobre</a></li>
                    <li><a href="{{ route('contact') }}" class="transition hover:text-white">Contato</a></li>
                </ul>
            </div>

            <div>
                <p class="text-gray-100 font-medium mb-3">Legal</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('terms') }}" class="transition hover:text-white">Termos de uso</a></li>
                    <li><a href="{{ route('privacy') }}" class="transition hover:text-white">Privacidade</a></li>
                </ul>
            </div>

            <div>
                <p class="text-gray-100 font-medium mb-3">Parceria</p>
                {{-- Discreto de propósito, sem CTA chamativo (task-12, seção 3). --}}
                <p class="text-gray-500">
                    É dono de lava-rápido?
                    <a href="{{ route('partners.register') }}" class="underline transition hover:text-white">Cadastre-se aqui</a>.
                </p>
            </div>
        </div>

        <div class="border-t border-white/5">
            <p class="max-w-6xl mx-auto px-4 py-5 text-xs text-gray-600">
                © {{ date('Y') }} Celke Wash Club — Celke Cursos
            </p>
        </div>
    </footer>
</body>
</html>
