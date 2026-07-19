@extends('layouts.admin')

@section('title', $subscription->user->name.' — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $subscription->user->name }}</h1>
        <x-badge :status="$subscription->status" />
    </div>

    <x-card class="mb-6">
        <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
            <div><dt class="inline font-medium">E-mail:</dt> <dd class="inline">{{ $subscription->user->email }}</dd></div>
            <div><dt class="inline font-medium">Plano:</dt> <dd class="inline">{{ $subscription->plan->name }}</dd></div>
            @if ($subscription->current_period_end)
                <div><dt class="inline font-medium">Renovação:</dt> <dd class="inline">{{ $subscription->current_period_end->format('d/m/Y') }}</dd></div>
            @endif
        </dl>
    </x-card>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Ciclos</h2>
    <x-data-table :rows="$subscription->cycles" empty-message="Nenhum ciclo ainda" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Período</x-data-table.th>
            <x-data-table.th>Cota</x-data-table.th>
        </x-slot:head>
        @foreach ($subscription->cycles as $cycle)
            <tr>
                <td class="px-4 py-3">{{ $cycle->period_start->format('d/m/Y') }} – {{ $cycle->period_end->format('d/m/Y') }}</td>
                <td class="px-4 py-3">{{ $cycle->quota_used }} / {{ $cycle->quota_total }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Pedidos</h2>
    <x-data-table :rows="$subscription->orders" empty-message="Nenhum pedido ainda" class="mb-6">
        <x-slot:head>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>
        @foreach ($subscription->orders as $order)
            <tr>
                <td class="px-4 py-3">R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3"><x-badge :status="$order->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Lavagens resgatadas</h2>
    <x-data-table :rows="$washRedemptions" empty-message="Nenhuma lavagem ainda">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>
        @foreach ($washRedemptions as $redemption)
            <tr>
                <td class="px-4 py-3">{{ $redemption->carWash->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$redemption->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                    {{ ($redemption->redeemed_at ?? $redemption->created_at)->format('d/m/Y H:i') }}
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
