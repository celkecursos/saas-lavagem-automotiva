@extends('layouts.admin')

@section('title', $user->name.' — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</h1>
        @if ($user->suspended_at)
            <x-badge status="suspended" variant="danger">suspenso</x-badge>
        @endif
    </div>

    <div class="flex flex-wrap gap-2 mb-6">
        @can('users.suspend')
            @if ($user->suspended_at === null)
                <x-confirm-modal :action="route('users.suspend', $user)"
                                 title="Suspender esta conta?"
                                 message="O login é bloqueado na hora, mesmo com sessão já ativa."
                                 confirm-label="Suspender">
                    <x-slot:trigger><button type="button" class="btn-danger">Suspender conta</button></x-slot:trigger>
                    <textarea name="suspension_reason" rows="3" required
                              placeholder="Motivo da suspensão (obrigatório)"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100"></textarea>
                </x-confirm-modal>
            @endif
        @endcan
        @can('users.reactivate')
            @if ($user->suspended_at !== null)
                <x-confirm-modal :action="route('users.reactivate', $user)"
                                 title="Reativar esta conta?"
                                 message="O usuário volta a conseguir logar normalmente."
                                 confirm-label="Reativar">
                    <x-slot:trigger><button type="button" class="btn-primary">Reativar conta</button></x-slot:trigger>
                </x-confirm-modal>
            @endif
        @endcan
        @can('users.show')
            @if (! $user->hasVerifiedEmail())
                <form method="POST" action="{{ route('users.resend-verification', $user) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Reenviar e-mail de verificação</button>
                </form>
            @endif
        @endcan
    </div>

    <x-card title="Dados pessoais" class="mb-6">
        <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
            <div><dt class="inline font-medium">E-mail:</dt> <dd class="inline">{{ $user->email }}</dd>
                @if ($user->hasVerifiedEmail())
                    <x-badge status="verified" variant="success">verificado</x-badge>
                @else
                    <x-badge status="unverified" variant="warning">não verificado</x-badge>
                @endif
            </div>
            @if ($user->phone)
                <div><dt class="inline font-medium">Telefone:</dt> <dd class="inline">{{ $user->phone }}</dd></div>
            @endif
            @if ($user->cpf)
                <div><dt class="inline font-medium">CPF:</dt> <dd class="inline">{{ $user->cpf }}</dd></div>
            @endif
            <div><dt class="inline font-medium">Cadastro:</dt> <dd class="inline">{{ $user->created_at->format('d/m/Y H:i') }}</dd></div>
            @if ($user->suspended_at)
                <div><dt class="inline font-medium">Suspenso em:</dt> <dd class="inline">{{ $user->suspended_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt class="inline font-medium">Motivo:</dt> <dd class="inline">{{ $user->suspension_reason }}</dd></div>
            @endif
        </dl>
    </x-card>

    @if ($user->carWashes->isNotEmpty())
        <x-card title="Vínculo com lava-rápido" class="mb-6">
            <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                @foreach ($user->carWashes as $carWash)
                    <li>
                        <a href="{{ route('car-washes.show', $carWash) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $carWash->name }}
                        </a>
                        — {{ $carWash->pivot->role === 'owner' ? 'dono' : 'funcionário' }}
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Assinaturas</h2>
    <x-data-table :rows="$user->subscriptions" empty-message="Nenhuma assinatura" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Plano</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Renovação</x-data-table.th>
        </x-slot:head>
        @foreach ($user->subscriptions as $subscription)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('subscriptions.show', $subscription) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $subscription->plan->name }}
                    </a>
                </td>
                <td class="px-4 py-3"><x-badge :status="$subscription->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $subscription->current_period_end?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Pedidos e pagamentos</h2>
    <x-data-table :rows="$user->orders" empty-message="Nenhum pedido" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>
        @foreach ($user->orders as $order)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}
                    </a>
                </td>
                <td class="px-4 py-3"><x-badge :status="$order->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Lavagens resgatadas</h2>
    <x-data-table :rows="$washRedemptions" empty-message="Nenhuma lavagem" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>
        @foreach ($washRedemptions as $redemption)
            <tr>
                <td class="px-4 py-3">{{ $redemption->carWash->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$redemption->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $redemption->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Veículos</h2>
    <x-data-table :rows="$user->vehicles" empty-message="Nenhum veículo" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Placa</x-data-table.th>
            <x-data-table.th>Marca/modelo</x-data-table.th>
        </x-slot:head>
        @foreach ($user->vehicles as $vehicle)
            <tr>
                <td class="px-4 py-3">{{ $vehicle->plate }}</td>
                <td class="px-4 py-3">{{ $vehicle->brand }} {{ $vehicle->model }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Avaliações feitas</h2>
    <x-data-table :rows="$user->carWashRatings" empty-message="Nenhuma avaliação" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Nota</x-data-table.th>
        </x-slot:head>
        @foreach ($user->carWashRatings as $rating)
            <tr>
                <td class="px-4 py-3">{{ $rating->carWash->name }}</td>
                <td class="px-4 py-3">{{ $rating->score }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Indicações</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-card title="Como indicador">
            @if ($user->referralsMade->isEmpty())
                <x-empty-state message="Nenhuma indicação feita" />
            @else
                <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                    @foreach ($user->referralsMade as $reward)
                        <li class="flex items-center justify-between">
                            <span>{{ $reward->referred->name }}</span>
                            <x-badge :status="$reward->status" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
        <x-card title="Como indicado">
            @if ($user->referralReceived->isEmpty())
                <x-empty-state message="Não veio de indicação" />
            @else
                <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300">
                    @foreach ($user->referralReceived as $reward)
                        <li class="flex items-center justify-between">
                            <span>{{ $reward->referrer->name }}</span>
                            <x-badge :status="$reward->status" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    @if ($user->cancellationRequestsMade->isNotEmpty())
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Solicitações de cancelamento abertas</h2>
        <x-data-table :rows="$user->cancellationRequestsMade" empty-message="Nenhuma solicitação aberta">
            <x-slot:head>
                <x-data-table.th>Motivo</x-data-table.th>
                <x-data-table.th>Data</x-data-table.th>
            </x-slot:head>
            @foreach ($user->cancellationRequestsMade as $request)
                <tr>
                    <td class="px-4 py-3">{{ $request->reason }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
@endsection
