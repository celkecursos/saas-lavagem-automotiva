@extends('layouts.public')

@section('title', 'Meus veículos — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Meus veículos</h1>
            <a href="{{ route('vehicles.create') }}" class="btn-primary">Adicionar veículo</a>
        </div>

        @if ($vehicles->isEmpty())
            <x-card>
                <x-empty-state message="Nenhum veículo cadastrado ainda." />
            </x-card>
        @else
            <div class="space-y-3">
                @foreach ($vehicles as $vehicle)
                    <x-card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-mono font-semibold text-gray-900 dark:text-gray-100">{{ $vehicle->plate }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ collect([$vehicle->brand, $vehicle->model, $vehicle->color])->filter()->join(' · ') ?: 'Sem detalhes adicionais' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('vehicles.washes', $vehicle) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Histórico</a>
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                                <x-confirm-modal :action="route('vehicles.destroy', $vehicle)" method="DELETE"
                                                 title="Remover este veículo?"
                                                 message="O histórico de lavagens dele continua disponível."
                                                 confirm-label="Remover">
                                    <x-slot:trigger><button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline cursor-pointer">Remover</button></x-slot:trigger>
                                </x-confirm-modal>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
@endsection
