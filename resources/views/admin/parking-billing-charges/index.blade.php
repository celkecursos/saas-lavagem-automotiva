@extends('layouts.admin')

@section('title', 'Cobranças do estacionamento — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Cobranças do estacionamento</h1>

        <a href="{{ route('parking-billing-charges.index', ['flagged' => request('flagged') ? null : 1]) }}" class="btn-secondary">
            {{ request('flagged') ? 'Ver todas' : 'Só sinalizadas' }}
        </a>
    </div>

    <x-data-table :rows="$charges" empty-message="Nenhuma cobrança encontrada">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Período</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Sinalizada</x-data-table.th>
        </x-slot:head>

        @foreach ($charges as $charge)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('parking-billing-charges.show', $charge) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $charge->carWash->name }}
                    </a>
                </td>
                <td class="px-4 py-3">{{ $charge->period_start->format('d/m/Y') }} – {{ $charge->period_end->format('d/m/Y') }}</td>
                <td class="px-4 py-3"><x-badge :status="$charge->status" /></td>
                <td class="px-4 py-3">
                    @if ($charge->flagged_for_review)
                        <x-badge status="flagged" variant="warning">sim</x-badge>
                    @else
                        <x-badge status="ok" variant="secondary">não</x-badge>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
