@extends('layouts.car-wash-panel')

@section('title', 'Repasses — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Repasses</h1>

    <x-data-table :rows="$payouts" empty-message="Nenhum repasse ainda">
        <x-slot:head>
            <x-data-table.th>Período</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
        </x-slot:head>

        @foreach ($payouts as $payout)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('panel.payouts.show', $payout) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $payout->period_start->format('d/m/Y') }} – {{ $payout->period_end->format('d/m/Y') }}
                    </a>
                </td>
                <td class="px-4 py-3">R$ {{ number_format($payout->total_amount_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3"><x-badge :status="$payout->status" /></td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
