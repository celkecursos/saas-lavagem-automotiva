@extends('layouts.public')

@section('title', 'Minha fidelidade — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Minha fidelidade</h1>

        <div class="flex items-center justify-between gap-4 mb-6">
            <x-stat-tile label="Saldo de pontos" :value="$balance" class="flex-1" />
            <a href="{{ route('loyalty.shop') }}" class="btn-primary shrink-0">Loja de recompensas</a>
        </div>

        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Conquistas desbloqueadas</h2>
        @if ($unlocked->isEmpty())
            <x-card class="mb-6"><x-empty-state message="Nenhuma conquista desbloqueada ainda." /></x-card>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                @foreach ($unlocked as $achievement)
                    <x-card class="text-center">
                        <div class="text-3xl mb-1">{{ $achievement->icon }}</div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $achievement->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->description }}</p>
                    </x-card>
                @endforeach
            </div>
        @endif

        @if ($locked->isNotEmpty())
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Conquistas a desbloquear</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                @foreach ($locked as $achievement)
                    <x-card class="text-center opacity-40 grayscale">
                        <div class="text-3xl mb-1">{{ $achievement->icon }}</div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $achievement->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $achievement->description }}</p>
                    </x-card>
                @endforeach
            </div>
        @endif

        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Extrato</h2>
        <x-data-table :rows="$ledger" empty-message="Nenhuma movimentação ainda">
            <x-slot:head>
                <x-data-table.th>Pontos</x-data-table.th>
                <x-data-table.th>Motivo</x-data-table.th>
                <x-data-table.th>Data</x-data-table.th>
            </x-slot:head>
            @foreach ($ledger as $entry)
                <tr>
                    <td class="px-4 py-3 {{ $entry->points >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $entry->points >= 0 ? '+' : '' }}{{ $entry->points }}
                    </td>
                    <td class="px-4 py-3">
                        {{ match ($entry->reason) {
                            'achievement' => 'Conquista',
                            'redemption' => 'Resgate',
                            default => 'Ajuste',
                        } }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
