@extends('layouts.admin')

@section('title', 'Ativação do clube de lavagem — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Ativação do clube de lavagem</h1>

        <form method="GET" action="{{ route('car-wash-product-subscriptions.index') }}">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                @foreach (['pending' => 'Pendentes', 'active' => 'Ativos', 'suspended' => 'Pausados', 'canceled' => 'Rejeitados/cancelados', 'all' => 'Todos'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$subscriptions" empty-message="Nenhuma solicitação nesta situação">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Plano de repasse escolhido</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Solicitado em</x-data-table.th>
            <x-data-table.th>Ações</x-data-table.th>
        </x-slot:head>

        @foreach ($subscriptions as $subscription)
            <tr>
                <td class="px-4 py-3">
                    @if (Route::has('car-washes.show'))
                        <a href="{{ route('car-washes.show', $subscription->carWash) }}"
                           class="font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $subscription->carWash->name }}</a>
                    @else
                        {{ $subscription->carWash->name }}
                    @endif
                </td>
                <td class="px-4 py-3">
                    {{ $subscription->payoutPlan?->label ?? '—' }}
                    @if ($subscription->payoutPlan)
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            R$ {{ number_format($subscription->payoutPlan->base_price_cents / 100, 2, ',', '.') }} por lavagem
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3"><x-badge :status="$subscription->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $subscription->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">
                    @if ($subscription->status === 'pending')
                        <div class="flex items-center gap-3">
                            @can('car-wash-product-subscriptions.approve')
                                <x-confirm-modal :action="route('car-wash-product-subscriptions.approve', $subscription)"
                                                 title="Aprovar a ativação do clube?"
                                                 message="O lava-rápido passa a aparecer pros assinantes e a receber resgates.">
                                    <x-slot:trigger>
                                        <button type="button" class="text-sm text-green-600 dark:text-green-400 hover:underline cursor-pointer">Aprovar</button>
                                    </x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                            @can('car-wash-product-subscriptions.reject')
                                <x-confirm-modal :action="route('car-wash-product-subscriptions.reject', $subscription)"
                                                 title="Rejeitar esta solicitação?"
                                                 message="O lava-rápido poderá solicitar novamente depois.">
                                    <x-slot:trigger>
                                        <button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline cursor-pointer">Rejeitar</button>
                                    </x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
