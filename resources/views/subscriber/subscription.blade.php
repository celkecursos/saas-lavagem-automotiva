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

                @if (in_array($subscription->status, ['active', 'past_due']))
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Renova em {{ $subscription->current_period_end?->format('d/m/Y') }}
                    </p>

                    @if ($subscription->cycles->isNotEmpty())
                        @php($cycle = $subscription->cycles->first())
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Cota do ciclo: {{ $cycle->quota_used }} / {{ $cycle->quota_total }}
                        </p>
                    @endif

                    @if ($subscription->pendingPlan)
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Troca agendada pra: <strong>{{ $subscription->pendingPlan->name }}</strong> (na próxima renovação)
                        </p>
                    @endif
                @elseif ($subscription->status === 'canceled')
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Seu acesso permanece até {{ $subscription->current_period_end?->format('d/m/Y') }}.
                    </p>
                @endif
            </x-card>

            @if ($subscription->status === 'active')
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <x-confirm-modal :action="route('subscription.cancel')"
                                     title="Cancelar sua assinatura?"
                                     message="Seu acesso continua até o fim do período já pago; a renovação futura não acontece mais."
                                     confirm-label="Cancelar assinatura">
                        <x-slot:trigger><button type="button" class="btn-danger">Cancelar assinatura</button></x-slot:trigger>
                    </x-confirm-modal>
                </div>
            @endif

            @if ($orders->isNotEmpty())
                <x-card title="Histórico de pedidos" class="mt-4">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                        @foreach ($orders as $order)
                            <li class="py-2 flex items-center justify-between">
                                <a href="{{ route('order.show', $order) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    Pedido #{{ $order->id }} — {{ $order->created_at->format('d/m/Y') }}
                                </a>
                                <span class="flex items-center gap-2">
                                    <span class="text-gray-500 dark:text-gray-400">R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}</span>
                                    <x-badge :status="$order->status" />
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        @endif
    </div>
@endsection
