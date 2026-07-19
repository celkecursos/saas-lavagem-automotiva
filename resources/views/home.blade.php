@extends('layouts.public')

@section('title', 'Celke Wash Club — Assine e lave seu carro em qualquer lava-rápido parceiro')
@section('meta_description', 'Lave seu carro quando quiser, em qualquer lava-rápido parceiro, por uma mensalidade só. Assine o Celke Wash Club e esqueça a preocupação de achar um lava-rápido de confiança.')

@section('content')
    {{-- Seção 1 — Hero: 100% focado no assinante (task-12, seção 2). --}}
    <section class="bg-backgroundsecond">
        <div class="max-w-5xl mx-auto px-4 py-20 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-100 mb-4">
                Lave seu carro quando quiser, em qualquer lava-rápido parceiro, por uma mensalidade só.
            </h1>
            <p class="text-gray-400 text-lg mb-8 max-w-2xl mx-auto">
                Chega de andar atrás de um lava-rápido de confiança toda vez. Um preço fixo
                e previsível, lavagens em qualquer unidade da rede.
            </p>
            @if (Route::has('plans.index'))
                <a href="{{ route('plans.index') }}" class="btn-primary text-base px-6 py-3 inline-block">Ver planos</a>
            @endif
        </div>
    </section>

    {{-- Seção 2 — Como funciona (task-12, seção 2). --}}
    <section id="como-funciona" class="max-w-5xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-10">Como funciona</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-4xl mb-3">1️⃣</div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Escolha seu plano</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Cotas de lavagem por mês, sem surpresa na fatura.</p>
            </div>
            <div>
                <div class="text-4xl mb-3">2️⃣</div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Escolha o lava-rápido mais perto</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Entre os parceiros aprovados da rede Celke Wash Club.</p>
            </div>
            <div>
                <div class="text-4xl mb-3">3️⃣</div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Resgate com um código</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Mostre o código no balcão e pronto, carro lavado.</p>
            </div>
        </div>
    </section>

    {{-- Seção 3 — Planos (mesma query/renderização de /planos, task-7). --}}
    <section id="planos" class="bg-white dark:bg-backgroundseconddark py-16">
        <div class="max-w-5xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-10">Planos e preços</h2>

            @if ($plans->isEmpty())
                <x-empty-state message="Nenhum plano disponível no momento." />
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($plans as $plan)
                        <x-card>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $plan->name }}</h3>
                            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($plan->price_cents / 100, 2, ',', '.') }}
                                <span class="text-sm font-normal text-gray-500">/{{ $plan->quota_period === 'monthly' ? 'mês' : $plan->quota_period }}</span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 mb-4">
                                {{ $plan->wash_quota }} lavagens por ciclo em qualquer lava-rápido da rede.
                            </p>

                            @if ($plan->features->isNotEmpty())
                                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 mb-4">
                                    @foreach ($plan->features as $feature)
                                        <li>✓ {{ $feature->label }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (Route::has('plans.checkout'))
                                <a href="{{ route('plans.checkout', $plan) }}" class="btn-primary w-full block text-center">Assinar</a>
                            @endif
                        </x-card>
                    @endforeach
                </div>

                @if (Route::has('plans.index'))
                    <p class="text-center mt-8">
                        <a href="{{ route('plans.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Ver todos os planos</a>
                    </p>
                @endif
            @endif
        </div>
    </section>

    {{-- Seção 4 — Lava-rápidos participantes (prova social, task-12, seção 2). --}}
    <section id="lava-rapidos" class="max-w-5xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-2">Lava-rápidos participantes</h2>
        <p class="text-center text-gray-600 dark:text-gray-400 mb-10">Tem lava-rápido parceiro perto de você.</p>

        @if ($carWashes->isEmpty())
            <x-empty-state message="Em breve, novos lava-rápidos parceiros." />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($carWashes as $carWash)
                    <x-card>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $carWash->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $carWash->city }} — {{ $carWash->state }}</p>
                    </x-card>
                @endforeach
            </div>

            @if (Route::has('ranking'))
                <p class="text-center mt-8">
                    <a href="{{ route('ranking') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Veja o ranking completo →</a>
                </p>
            @endif
        @endif
    </section>

    {{-- Seção 5 — FAQ (task-12, seção 2). --}}
    <section id="faq" class="bg-white dark:bg-backgroundseconddark py-16">
        <div class="max-w-3xl mx-auto px-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-10">Perguntas frequentes</h2>

            <div class="space-y-4" x-data="{ open: null }">
                @foreach ([
                    ['q' => 'Como eu cancelo minha assinatura?', 'a' => 'Direto no seu painel, a qualquer momento. Seu acesso continua até o fim do período já pago — só a renovação futura não acontece mais.'],
                    ['q' => 'O que acontece se eu não usar toda a minha cota?', 'a' => 'Depende do plano: alguns acumulam (rollover) a cota não usada pro próximo ciclo, outros zeram. Isso fica claro na vitrine de cada plano.'],
                    ['q' => 'Funciona em qualquer lava-rápido mesmo?', 'a' => 'Funciona em qualquer lava-rápido parceiro aprovado da rede Celke Wash Club — veja a lista de participantes acima.'],
                ] as $index => $item)
                    <x-card>
                        <button type="button" class="w-full flex items-center justify-between text-left font-medium text-gray-900 dark:text-gray-100"
                                @click="open = open === {{ $index }} ? null : {{ $index }}">
                            {{ $item['q'] }}
                            <span x-text="open === {{ $index }} ? '−' : '+'"></span>
                        </button>
                        <p x-show="open === {{ $index }}" x-cloak class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            {{ $item['a'] }}
                        </p>
                    </x-card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Seção 6 — Parceria: deliberadamente discreta, mais abaixo, cor
         Secondary (task-12, seção 2). --}}
    <section class="bg-gray-100 dark:bg-backgrounddark py-10">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <p class="text-gray-700 dark:text-gray-300 mb-3">
                Tem um lava-rápido ou estacionamento? Seja parceiro Celke Wash Club e ganhe
                uma fonte extra de clientes.
            </p>
            @if (Route::has('partners.register'))
                <a href="{{ route('partners.register') }}" class="btn-secondary inline-block">Quero ser parceiro</a>
            @endif
        </div>
    </section>
@endsection
