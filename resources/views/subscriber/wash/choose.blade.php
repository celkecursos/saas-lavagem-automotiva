@extends('layouts.public')

@section('title', 'Escolher lava-rápido — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Resgatar lavagem</h1>

        @if ($activeRedemption)
            {{-- Código ativo em destaque enquanto 'requested' e não
                 expirado (task-8, seção 3). --}}
            <x-card title="Seu código">
                <p class="text-4xl font-mono font-bold tracking-widest text-gray-900 dark:text-gray-100 text-center py-4">
                    {{ $activeRedemption->confirmation_code }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-4">
                    Mostre esse código no balcão de <strong>{{ $activeRedemption->carWash->name }}</strong>.
                    Válido até {{ $activeRedemption->code_expires_at->format('H:i') }}.
                </p>
                <x-confirm-modal :action="route('wash.cancel', $activeRedemption)"
                                 title="Cancelar este código?"
                                 message="Você pode gerar outro depois."
                                 confirm-label="Cancelar código">
                    <x-slot:trigger><button type="button" class="btn-danger w-full">Cancelar código</button></x-slot:trigger>
                </x-confirm-modal>
            </x-card>
        @else
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Lava-rápidos disponíveis</h2>

            @if ($carWashes->isEmpty())
                <x-empty-state message="Nenhum lava-rápido disponível no momento." />
            @else
                <div class="space-y-3">
                    @foreach ($carWashes as $carWash)
                        <x-card>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $carWash->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $carWash->city }}/{{ $carWash->state }}</p>
                                </div>
                                <form method="POST" action="{{ route('wash.request', $carWash) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary">Gerar código</button>
                                </form>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @endif
        @endif

        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mt-8 mb-3">Histórico de lavagens</h2>

        <x-data-table :rows="$history" empty-message="Nenhuma lavagem ainda">
            <x-slot:head>
                <x-data-table.th>Lava-rápido</x-data-table.th>
                <x-data-table.th>Status</x-data-table.th>
                <x-data-table.th>Data</x-data-table.th>
                <x-data-table.th>Avaliação</x-data-table.th>
            </x-slot:head>

            @foreach ($history as $redemption)
                <tr>
                    <td class="px-4 py-3">{{ $redemption->carWash->name }}</td>
                    <td class="px-4 py-3"><x-badge :status="$redemption->status" /></td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                        {{ ($redemption->redeemed_at ?? $redemption->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        {{-- Convite pra avaliar, não bloqueante (task-8, §2, passo 7). --}}
                        @if ($redemption->status === 'completed')
                            <form method="POST" action="{{ route('wash.rate', $redemption) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="score" class="rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm">
                                    @foreach ([100, 90, 80, 70, 50, 0] as $option)
                                        <option value="{{ $option }}" @selected($redemption->rating?->score === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                    {{ $redemption->rating ? 'Editar avaliação' : 'Avaliar' }}
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
