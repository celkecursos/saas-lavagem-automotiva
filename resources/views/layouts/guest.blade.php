<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head', ['pageTitle' => $title ?? config('app.name')])
</head>
{{-- Shell das telas de autenticação: split screen com a marca à esquerda
     (só em lg+) e o formulário à direita. Usa o head compartilhado, então
     herda fontes locais, favicon e dark mode do resto do projeto — o
     layout original do Breeze puxava fonte de CDN externo. --}}
<body class="min-h-screen bg-gray-50 dark:bg-backgrounddark">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Painel de marca: escondido no mobile pra não empurrar o
             formulário pra baixo da dobra. --}}
        <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-backgroundsecond p-12">
            <div class="pointer-events-none absolute -top-32 -left-24 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-24 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>

            <a href="{{ url('/') }}" class="relative flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="44" height="44" class="h-11 w-11">
                <span class="text-lg font-semibold tracking-tight text-white">Celke Wash Club</span>
            </a>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-bold leading-tight text-white">
                    Seu carro limpo, <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">sem complicação</span>.
                </h2>
                <p class="mt-4 text-gray-400">
                    Uma mensalidade só, lavagens em qualquer lava-rápido parceiro da rede.
                </p>

                <ul class="mt-8 space-y-3 text-sm text-gray-300">
                    @foreach (['Sem fidelidade e sem taxa de adesão', 'Resgate em segundos com um código', 'Rede de parceiros aprovados'] as $item)
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-gray-600">© {{ date('Y') }} Celke Wash Club — Celke Cursos</p>
        </div>

        {{-- Formulário --}}
        <div class="flex min-h-screen flex-col justify-center px-6 py-12 lg:min-h-0 lg:px-16">
            <div class="mx-auto w-full max-w-md">
                {{-- Logo repetida no topo do formulário só no mobile, onde o
                     painel de marca não aparece. --}}
                <a href="{{ url('/') }}" class="mb-10 flex items-center justify-center gap-3 lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" width="40" height="40" class="h-10 w-10">
                    <span class="font-semibold tracking-tight text-gray-900 dark:text-white">Celke Wash Club</span>
                </a>

                @isset($heading)
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $heading }}</h1>
                @endisset
                @isset($subheading)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $subheading }}</p>
                @endisset

                <div class="mt-8">
                    {{ $slot }}
                </div>

                <p class="mt-10 text-center text-xs text-gray-500 dark:text-gray-600">
                    <a href="{{ url('/') }}" class="hover:text-gray-700 dark:hover:text-gray-400">← Voltar para o site</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
