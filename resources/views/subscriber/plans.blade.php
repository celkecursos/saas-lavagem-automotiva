@extends('layouts.public')

@section('title', 'Planos — Celke Wash Club')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Escolha seu plano</h1>

        @if ($plans->isEmpty())
            <x-empty-state message="Nenhum plano disponível no momento." />
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($plans as $plan)
                    <x-card>
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h2>
                            @if ($currentPlanId === $plan->id)
                                <x-badge status="active">seu plano atual</x-badge>
                            @endif
                        </div>

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

                        @if ($currentPlanId !== $plan->id)
                            {{-- Guest: o middleware 'auth' do checkout já redireciona pro
                                 login guardando a URL interna (session url.intended) —
                                 redirect()->intended() volta pra cá sozinho depois
                                 (nunca aceita URL externa, é 100% Laravel). --}}
                            <a href="{{ route('plans.checkout', $plan) }}"
                               class="btn-primary w-full block text-center">Assinar</a>
                        @endif
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
@endsection
