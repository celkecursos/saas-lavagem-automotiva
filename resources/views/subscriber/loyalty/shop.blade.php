@extends('layouts.public')

@section('title', 'Loja de recompensas — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Loja de recompensas</h1>
            <a href="{{ route('loyalty.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Minha fidelidade</a>
        </div>

        <x-stat-tile label="Saldo de pontos" :value="$balance" class="mb-6" />

        @if ($redemptions->isEmpty())
            <x-card><x-empty-state message="Nenhuma recompensa disponível no momento." /></x-card>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($redemptions as $redemption)
                    <x-card :title="$redemption->name">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $redemption->points_cost }} pontos</p>

                        <form method="POST" action="{{ route('loyalty.shop.redeem', $redemption) }}">
                            @csrf
                            <button type="submit" class="btn-primary w-full" @disabled($balance < $redemption->points_cost)>
                                {{ $balance < $redemption->points_cost ? 'Saldo insuficiente' : 'Resgatar' }}
                            </button>
                        </form>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
@endsection
