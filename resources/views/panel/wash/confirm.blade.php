@extends('layouts.car-wash-panel')

@section('title', 'Confirmar lavagem — Painel')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Confirmar lavagem</h1>

        @if (session('error') || $errors->any() || isset($lookupError))
            <div class="alert-danger">
                {{ session('error') ?: ($errors->any() ? $errors->first() : ($lookupError ?? '')) }}
            </div>
        @endif

        @if (! isset($preview))
            {{-- Passo 1: busca o código (task-15, seção 3). --}}
            <x-card>
                <form method="POST" action="{{ route('panel.washes.confirm.lookup') }}">
                    @csrf
                    <x-form-field label="Código de confirmação" name="confirmation_code"
                                  inputmode="numeric" maxlength="6" autofocus />
                    <button type="submit" class="btn-primary w-full">Buscar</button>
                </form>
            </x-card>
        @else
            {{-- Passo 2: conferência visual do veículo antes de aceitar
                 (task-15, seção 3) — placa + marca/modelo/cor junto do
                 código, pra reduzir erro de confirmar o carro errado. --}}
            <x-card title="Confira o veículo antes de confirmar">
                <p class="text-3xl font-mono font-bold tracking-widest text-gray-900 dark:text-gray-100 text-center py-2">
                    {{ $preview->confirmation_code }}
                </p>

                @if ($preview->vehicle)
                    <p class="text-center font-mono text-lg text-gray-900 dark:text-gray-100">{{ $preview->vehicle->plate }}</p>
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ collect([$preview->vehicle->brand, $preview->vehicle->model, $preview->vehicle->color])->filter()->join(' · ') ?: 'Sem detalhes adicionais' }}
                    </p>
                @else
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mb-4">Sem veículo informado.</p>
                @endif

                <form method="POST" action="{{ route('panel.washes.confirm.store') }}">
                    @csrf
                    <input type="hidden" name="confirmation_code" value="{{ $preview->confirmation_code }}">
                    <button type="submit" class="btn-primary w-full">Confirmar lavagem</button>
                </form>

                <a href="{{ route('panel.washes.confirm') }}" class="block text-center text-sm text-gray-500 dark:text-gray-400 hover:underline mt-3">Cancelar / buscar outro código</a>
            </x-card>
        @endif
    </div>
@endsection
