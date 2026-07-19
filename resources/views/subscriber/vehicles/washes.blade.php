@extends('layouts.public')

@section('title', 'Histórico — '.$vehicle->plate)

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">
            Histórico — <span class="font-mono">{{ $vehicle->plate }}</span>
        </h1>

        <x-data-table :rows="$redemptions" empty-message="Nenhuma lavagem ainda pra este veículo">
            <x-slot:head>
                <x-data-table.th>Lava-rápido</x-data-table.th>
                <x-data-table.th>Status</x-data-table.th>
                <x-data-table.th>Data</x-data-table.th>
            </x-slot:head>

            @foreach ($redemptions as $redemption)
                <tr>
                    <td class="px-4 py-3">{{ $redemption->carWash->name }}</td>
                    <td class="px-4 py-3"><x-badge :status="$redemption->status" /></td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                        {{ ($redemption->redeemed_at ?? $redemption->created_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
