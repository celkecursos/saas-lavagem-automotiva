@extends('layouts.public')

@section('title', 'Celke Wash Club — Assine e lave seu carro em qualquer lava-rápido parceiro')
@section('meta_description', 'Lave seu carro quando quiser, em qualquer lava-rápido parceiro, por uma mensalidade só. Assine o Celke Wash Club e esqueça a preocupação de achar um lava-rápido de confiança.')

@php
    // Destaque visual do plano "mais vendido": só faz sentido com 3+ planos
    // na vitrine, e aí o do meio é o que a gente quer empurrar. Com 1 ou 2
    // planos ninguém ganha destaque (evita card gigante sozinho na tela).
    $featuredIndex = $plans->count() >= 3 ? intdiv($plans->count() - 1, 2) : null;
@endphp

@section('content')
    {{-- Seção 1 — Hero: 100% focado no assinante (task-12, seção 2). --}}
    <section class="relative overflow-hidden bg-backgroundsecond">
        {{-- Brilhos decorativos: puro CSS, sem asset extra pra carregar. --}}
        <div class="pointer-events-none absolute -top-40 -left-32 h-96 w-96 rounded-full bg-blue-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-24 h-96 w-96 rounded-full bg-cyan-500/10 blur-3xl"></div>

        <div class="relative max-w-6xl mx-auto px-4 py-20 lg:py-28 grid lg:grid-cols-2 gap-14 items-center">
            <div class="text-center lg:text-left">
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-500/10 px-3 py-1 text-xs font-medium text-blue-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-400"></span>
                    </span>
                    Rede de lava-rápidos parceiros
                </span>

                <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white leading-[1.1]">
                    Lave seu carro quando quiser,<br class="hidden sm:block">
                    por uma <span class="bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">mensalidade só</span>.
                </h1>

                <p class="mt-6 text-lg text-gray-400 max-w-xl mx-auto lg:mx-0">
                    Chega de andar atrás de um lava-rápido de confiança toda vez. Um preço fixo
                    e previsível, lavagens em qualquer unidade da rede.
                </p>

                <div class="mt-9 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                    @if (Route::has('plans.index'))
                        <a href="{{ route('plans.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-500 px-7 py-3.5 text-base font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
                            Ver planos
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @endif
                    <a href="{{ url('/#como-funciona') }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-700 px-7 py-3.5 text-base font-medium text-gray-300 transition hover:border-gray-500 hover:text-white">
                        Como funciona
                    </a>
                </div>

                <dl class="mt-12 grid grid-cols-3 gap-6 max-w-md mx-auto lg:mx-0 border-t border-gray-800 pt-8">
                    <div>
                        <dt class="text-2xl font-bold text-white">{{ $carWashes->count() ?: '—' }}</dt>
                        <dd class="text-xs text-gray-500 mt-0.5">Parceiros na rede</dd>
                    </div>
                    <div>
                        <dt class="text-2xl font-bold text-white">0</dt>
                        <dd class="text-xs text-gray-500 mt-0.5">Taxa de adesão</dd>
                    </div>
                    <div>
                        <dt class="text-2xl font-bold text-white">1 min</dt>
                        <dd class="text-xs text-gray-500 mt-0.5">Pra resgatar</dd>
                    </div>
                </dl>
            </div>

            {{-- Mockup do código de resgate: mostra o produto sem depender de
                 foto de banco de imagem. --}}
            <div class="hidden lg:block">
                <div class="relative mx-auto max-w-sm rotate-1 rounded-2xl border border-gray-700/60 bg-gradient-to-br from-backgroundthird to-backgroundfourth p-6 shadow-2xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/logo.png') }}" alt="" width="28" height="28" class="h-7 w-7">
                            <span class="text-sm font-semibold text-gray-200">Wash Club</span>
                        </div>
                        <span class="rounded-full bg-green-500/15 px-2.5 py-0.5 text-xs font-medium text-green-400">Ativo</span>
                    </div>

                    <p class="mt-8 text-xs uppercase tracking-widest text-gray-500">Seu código de lavagem</p>
                    <p class="mt-2 font-mono text-3xl font-bold tracking-[0.25em] text-white">7K2P9</p>

                    <div class="mt-8 border-t border-gray-700/60 pt-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Lavagens restantes</span>
                            <span class="font-semibold text-gray-200">3 de 4</span>
                        </div>
                        <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-gray-700">
                            <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Seção 2 — Como funciona (task-12, seção 2). --}}
    <section id="como-funciona" class="bg-gray-50 dark:bg-backgrounddark py-20 lg:py-24">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Simples assim</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Como funciona</h2>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    Três passos entre assinar e sair com o carro limpo.
                </p>
            </div>

            <div class="relative mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Linha conectando os passos, só no desktop. --}}
                <div class="pointer-events-none absolute left-0 right-0 top-8 hidden md:block">
                    <div class="mx-auto h-px w-2/3 bg-gradient-to-r from-transparent via-gray-300 to-transparent dark:via-gray-700"></div>
                </div>

                @foreach ([
                    ['title' => 'Escolha seu plano', 'text' => 'Cotas de lavagem por mês, sem surpresa na fatura.', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
                    ['title' => 'Escolha o lava-rápido', 'text' => 'Entre os parceiros aprovados, o que estiver mais perto de você.', 'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
                    ['title' => 'Resgate com um código', 'text' => 'Mostre o código no balcão e pronto, carro lavado.', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ] as $index => $step)
                    <div class="relative text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-backgroundseconddark dark:ring-gray-800">
                            <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}" />
                            </svg>
                        </div>
                        <span class="mt-5 inline-block text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-600">
                            Passo {{ $index + 1 }}
                        </span>
                        <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 max-w-xs mx-auto">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Seção 3 — Planos (mesma query/renderização de /planos, task-7). --}}
    <section id="planos" class="bg-white dark:bg-backgroundseconddark py-20 lg:py-24">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Planos</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Escolha o tamanho da sua rotina</h2>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    Todos sem fidelidade — cancele quando quiser, direto no painel.
                </p>
            </div>

            @if ($plans->isEmpty())
                <div class="mt-12">
                    <x-empty-state message="Nenhum plano disponível no momento." />
                </div>
            @else
                <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-start">
                    @foreach ($plans as $plan)
                        @php $featured = $loop->index === $featuredIndex; @endphp

                        <div @class([
                            'relative rounded-2xl p-7 transition',
                            'border-2 border-blue-500 bg-white shadow-xl shadow-blue-500/10 md:-mt-4 md:pb-10 dark:bg-backgroundthirddark' => $featured,
                            'border border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:bg-backgroundthirddark dark:hover:border-gray-700' => ! $featured,
                        ])>
                            @if ($featured)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-blue-500 px-3 py-1 text-xs font-semibold text-white shadow">
                                    Mais vendido
                                </span>
                            @endif

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h3>

                            <p class="mt-4 flex items-baseline gap-1">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">R$</span>
                                <span class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                                    {{ number_format($plan->price_cents / 100, 2, ',', '.') }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    /{{ $plan->quota_period === 'monthly' ? 'mês' : $plan->quota_period }}
                                </span>
                            </p>

                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $plan->wash_quota }} lavagens por ciclo em qualquer lava-rápido da rede.
                            </p>

                            @if (Route::has('plans.checkout'))
                                <a href="{{ route('plans.checkout', $plan) }}"
                                   @class([
                                       'mt-6 block rounded-lg px-4 py-3 text-center text-sm font-semibold transition',
                                       'bg-blue-500 text-white shadow-lg shadow-blue-500/25 hover:bg-blue-600' => $featured,
                                       'border border-gray-300 text-gray-900 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-800' => ! $featured,
                                   ])>
                                    Assinar
                                </a>
                            @endif

                            @if ($plan->features->isNotEmpty())
                                <ul class="mt-7 space-y-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    @foreach ($plan->features as $feature)
                                        <li class="flex items-start gap-2.5 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            {{ $feature->label }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if (Route::has('plans.index'))
                    <p class="text-center mt-10">
                        <a href="{{ route('plans.index') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                            Comparar todos os planos →
                        </a>
                    </p>
                @endif
            @endif
        </div>
    </section>

    {{-- Seção 4 — Lava-rápidos participantes (prova social, task-12, seção 2). --}}
    <section id="lava-rapidos" class="bg-gray-50 dark:bg-backgrounddark py-20 lg:py-24">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">A rede</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Lava-rápidos participantes</h2>
                <p class="mt-4 text-gray-600 dark:text-gray-400">Tem lava-rápido parceiro perto de você.</p>
            </div>

            @if ($carWashes->isEmpty())
                <div class="mt-12">
                    <x-empty-state message="Em breve, novos lava-rápidos parceiros." />
                </div>
            @else
                <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($carWashes as $carWash)
                        <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 transition hover:border-gray-300 hover:shadow-sm dark:border-gray-800 dark:bg-backgroundseconddark dark:hover:border-gray-700">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-400 text-base font-bold text-white">
                                {{ mb_strtoupper(mb_substr($carWash->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-900 dark:text-white">{{ $carWash->name }}</p>
                                <p class="mt-0.5 flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span class="truncate">{{ $carWash->city }} — {{ $carWash->state }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (Route::has('ranking'))
                    <p class="text-center mt-10">
                        <a href="{{ route('ranking') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                            Veja o ranking completo →
                        </a>
                    </p>
                @endif
            @endif
        </div>
    </section>

    {{-- Seção 5 — FAQ (task-12, seção 2). --}}
    <section id="faq" class="bg-white dark:bg-backgroundseconddark py-20 lg:py-24">
        <div class="max-w-3xl mx-auto px-4">
            <div class="text-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">Dúvidas</span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Perguntas frequentes</h2>
            </div>

            <div class="mt-12 divide-y divide-gray-200 rounded-2xl border border-gray-200 dark:divide-gray-800 dark:border-gray-800" x-data="{ open: null }">
                @foreach ([
                    ['q' => 'Como eu cancelo minha assinatura?', 'a' => 'Direto no seu painel, a qualquer momento. Seu acesso continua até o fim do período já pago — só a renovação futura não acontece mais.'],
                    ['q' => 'O que acontece se eu não usar toda a minha cota?', 'a' => 'Depende do plano: alguns acumulam (rollover) a cota não usada pro próximo ciclo, outros zeram. Isso fica claro na vitrine de cada plano.'],
                    ['q' => 'Funciona em qualquer lava-rápido mesmo?', 'a' => 'Funciona em qualquer lava-rápido parceiro aprovado da rede Celke Wash Club — veja a lista de participantes acima.'],
                ] as $index => $item)
                    <div>
                        <button type="button"
                                class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left font-medium text-gray-900 transition hover:bg-gray-50 dark:text-white dark:hover:bg-white/5"
                                @click="open = open === {{ $index }} ? null : {{ $index }}">
                            {{ $item['q'] }}
                            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200"
                                 :class="open === {{ $index }} && 'rotate-180'"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <p x-show="open === {{ $index }}" x-cloak
                           x-transition:enter="transition ease-out duration-150"
                           x-transition:enter-start="opacity-0 -translate-y-1"
                           x-transition:enter-end="opacity-100 translate-y-0"
                           class="px-6 pb-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                            {{ $item['a'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Seção 6 — Parceria: deliberadamente discreta, mais abaixo, cor
         Secondary (task-12, seção 2). --}}
    <section class="bg-gray-100 dark:bg-backgrounddark py-14">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex flex-col items-center gap-5 rounded-2xl border border-gray-200 bg-white px-8 py-8 text-center sm:flex-row sm:justify-between sm:text-left dark:border-gray-800 dark:bg-backgroundseconddark">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Tem um lava-rápido ou estacionamento?</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Seja parceiro Celke Wash Club e ganhe uma fonte extra de clientes.
                    </p>
                </div>
                @if (Route::has('partners.register'))
                    <a href="{{ route('partners.register') }}" class="btn-secondary shrink-0">Quero ser parceiro</a>
                @endif
            </div>
        </div>
    </section>
@endsection
