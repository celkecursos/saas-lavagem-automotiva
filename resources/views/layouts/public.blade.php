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

                @auth
                    {{-- Menu do usuário logado: só o primeiro nome, pra não
                         estourar a largura do header com nome composto. O
                         <x-dropdown> do Breeze não serve aqui — ele é claro
                         (bg-white) e o header é escuro. --}}
                    <div class="relative hidden sm:block" x-data="{ userMenu: false }" @click.outside="userMenu = false">
                        <button type="button" @click="userMenu = ! userMenu"
                                class="flex items-center gap-2 transition hover:text-white">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 text-xs font-bold text-white">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span>{{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') }}</span>
                            <svg class="h-4 w-4 transition-transform duration-200" :class="userMenu && 'rotate-180'"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="userMenu" x-cloak
                             class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-backgroundseconddark">
                            <p class="truncate border-b border-gray-100 px-4 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                {{ auth()->user()->email }}
                            </p>

                            {{-- /dashboard já resolve o destino conforme o
                                 perfil: admin -> /admin, lava-rápido ->
                                 /painel, assinante -> /assinatura. --}}
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-backgroundthirddark">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                Dashboard
                            </a>

                            @if (Route::has('logout'))
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex w-full items-center gap-2.5 border-t border-gray-100 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-backgroundthirddark">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                        </svg>
                                        Sair
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="hidden sm:inline transition hover:text-white">Entrar</a>
                    @endif
                    @if (Route::has('plans.index'))
                        <a href="{{ route('plans.index') }}"
                           class="hidden sm:inline-flex items-center justify-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-600">
                            Assinar agora
                        </a>
                    @endif
                @endauth

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
            @auth
                <div class="mt-2 border-t border-white/5 pt-2">
                    <p class="px-2 py-1 text-xs text-gray-600">
                        {{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') }} — {{ auth()->user()->email }}
                    </p>
                    <a href="{{ route('dashboard') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white">Dashboard</a>
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded px-2 py-2 text-left hover:bg-white/5 hover:text-white">Sair</button>
                        </form>
                    @endif
                </div>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="block rounded px-2 py-2 hover:bg-white/5 hover:text-white sm:hidden">Entrar</a>
                @endif
            @endauth
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
