@extends('layouts.car-wash-panel')

@section('title', 'Detalhe do repasse — Painel')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $payout->period_start->format('d/m/Y') }} – {{ $payout->period_end->format('d/m/Y') }}
        </h1>
        <x-badge :status="$payout->status" />
    </div>

    <x-stat-tile label="Valor total" :value="'R$ '.number_format($payout->total_amount_cents / 100, 2, ',', '.')" class="mb-6" />

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Lavagens que compõem este valor</h2>

    <x-data-table :rows="$payout->items" empty-message="Nenhum item">
        <x-slot:head>
            <x-data-table.th>Lavagem #</x-data-table.th>
            <x-data-table.th>Confirmada em</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
        </x-slot:head>

        @foreach ($payout->items as $item)
            <tr>
                <td class="px-4 py-3">#{{ $item->wash_redemption_id }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item->washRedemption->redeemed_at?->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">R$ {{ number_format($item->amount_cents / 100, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
