@extends('layouts.car-wash-panel')

@section('title', 'Lavagens — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Lavagens</h1>

    <x-data-table :rows="$redemptions" empty-message="Nenhuma lavagem ainda">
        <x-slot:head>
            <x-data-table.th>Veículo</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Confirmado por</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>

        @foreach ($redemptions as $redemption)
            <tr>
                <td class="px-4 py-3">{{ $redemption->vehicle?->plate ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge :status="$redemption->status" /></td>
                <td class="px-4 py-3">{{ $redemption->confirmedBy?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                    {{ ($redemption->redeemed_at ?? $redemption->created_at)->format('d/m/Y H:i') }}
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
