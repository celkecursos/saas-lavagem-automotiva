@extends('layouts.public')

@section('title', 'Ranking — Lava-rápido do mês — Celke Wash Club')
@section('meta_description', 'Veja os lava-rápidos parceiros mais bem avaliados do mês no Celke Wash Club.')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center mb-2">Lava-rápido do mês</h1>
        <p class="text-center text-gray-600 dark:text-gray-400 mb-10">
            Os lava-rápidos parceiros mais bem avaliados de {{ now()->translatedFormat('F/Y') }}.
        </p>

        @if ($ranking->isEmpty())
            <x-empty-state message="Ainda não há avaliações suficientes este mês pra montar o ranking." />
        @else
            <x-data-table :rows="$ranking" empty-message="Ainda não há avaliações suficientes este mês.">
                <x-slot:head>
                    <x-data-table.th>Posição</x-data-table.th>
                    <x-data-table.th>Lava-rápido</x-data-table.th>
                    <x-data-table.th>Cidade</x-data-table.th>
                    <x-data-table.th>Nota média do mês</x-data-table.th>
                </x-slot:head>

                @foreach ($ranking as $index => $carWash)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">
                            {{ $index + 1 }}º
                            @if ($index === 0)
                                <span class="ml-1" title="Lava-rápido do mês">🏆</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $carWash->name }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $carWash->city }} — {{ $carWash->state }}</td>
                        <td class="px-4 py-3">{{ number_format($carWash->month_average_score, 1, ',', '.') }}</td>
                    </tr>
                @endforeach
            </x-data-table>
        @endif
    </div>
@endsection
