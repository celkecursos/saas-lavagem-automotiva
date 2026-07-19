@extends('layouts.public')

@section('title', 'Planos — Celke Wash Club')

@php
    // Mesmo critério de destaque da home: só com 3+ planos, o do meio.
    $featuredIndex = $plans->count() >= 3 ? intdiv($plans->count() - 1, 2) : null;
@endphp

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-16">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Escolha seu plano</h1>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Sem fidelidade e sem taxa de adesão — cancele quando quiser, direto no painel.
            </p>
        </div>

        @if ($plans->isEmpty())
            <div class="mt-12">
                <x-empty-state message="Nenhum plano disponível no momento." />
            </div>
        @else
            <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-start">
                @foreach ($plans as $plan)
                    @php
                        $isCurrent = $currentPlanId === $plan->id;
                        $featured = $loop->index === $featuredIndex;
                    @endphp

                    <div @class([
                        'relative rounded-2xl p-7 transition',
                        'border-2 border-blue-500 bg-white shadow-xl shadow-blue-500/10 md:-mt-4 md:pb-10 dark:bg-backgroundthirddark' => $featured,
                        'border border-gray-200 bg-white hover:border-gray-300 hover:shadow-md dark:border-gray-800 dark:bg-backgroundthirddark dark:hover:border-gray-700' => ! $featured,
                    ])>
                        @if ($featured && ! $isCurrent)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-blue-500 px-3 py-1 text-xs font-semibold text-white shadow">
                                Mais vendido
                            </span>
                        @endif

                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</h2>
                            @if ($isCurrent)
                                <x-badge status="active">seu plano atual</x-badge>
                            @endif
                        </div>

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

                        @if (! $isCurrent)
                            {{-- Guest: o middleware 'auth' do checkout já redireciona pro
                                 login guardando a URL interna (session url.intended) —
                                 redirect()->intended() volta pra cá sozinho depois
                                 (nunca aceita URL externa, é 100% Laravel). --}}
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
        @endif
    </div>
@endsection
