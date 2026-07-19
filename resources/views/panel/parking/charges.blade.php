@extends('layouts.car-wash-panel')

@section('title', 'Cobranças do estacionamento — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Cobranças do estacionamento</h1>

    <x-data-table :rows="$charges" empty-message="Nenhuma cobrança ainda">
        <x-slot:head>
            <x-data-table.th>Período</x-data-table.th>
            <x-data-table.th>Lavagens no período</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($charges as $charge)
            <tr>
                <td class="px-4 py-3">{{ $charge->period_start->format('d/m/Y') }} – {{ $charge->period_end->format('d/m/Y') }}</td>
                <td class="px-4 py-3">{{ $charge->wash_count }} / {{ $charge->total_spots_snapshot }} vagas</td>
                <td class="px-4 py-3">
                    <x-badge :status="$charge->status" />
                    @if ($charge->flagged_for_review)
                        <x-badge status="flagged" variant="warning">em revisão</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if ($charge->is_free)
                        —
                    @else
                        R$ {{ number_format($charge->fee_amount_cents / 100, 2, ',', '.') }}
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if ($charge->status === 'pending')
                        <a href="{{ route('panel.parking.charges.checkout', $charge) }}" class="btn-primary">Pagar</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
