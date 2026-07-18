@extends('layouts.public')

@section('title', 'Minha assinatura — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Minha assinatura</h1>

        @if ($subscription === null)
            <x-card>
                <x-empty-state message="Você ainda não tem uma assinatura." />
                @if (Route::has('plans.index'))
                    <a href="{{ route('plans.index') }}" class="btn-primary mt-4 block text-center">Ver planos</a>
                @endif
            </x-card>
        @else
            <x-card :title="$subscription->plan->name">
                <p class="text-sm mb-2">Status: <x-badge :status="$subscription->status" /></p>

                @if ($subscription->status === 'active')
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Renova em {{ $subscription->current_period_end?->format('d/m/Y') }}
                    </p>

                    @if ($subscription->cycles->isNotEmpty())
                        @php($cycle = $subscription->cycles->first())
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Cota do ciclo: {{ $cycle->quota_used }} / {{ $cycle->quota_total }}
                        </p>
                    @endif
                @endif
            </x-card>
        @endif
    </div>
@endsection
