@extends('layouts.car-wash-panel')

@section('title', 'Estacionamento — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Meu estacionamento</h1>

    @if ($parkingLot === null)
        <x-card title="Cadastre seu estacionamento">
            <form method="POST" action="{{ route('panel.parking.lot.store') }}">
                @csrf
                <x-form-field label="Nome" name="name" placeholder="ex: Pátio Principal" required />
                <x-form-field label="Total de vagas" name="total_spots" type="number" required />
                <button type="submit" class="btn-primary">Salvar</button>
            </form>
        </x-card>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <x-stat-tile label="Vagas livres" :value="$freeSpots" />
            <x-stat-tile label="Vagas ocupadas" :value="$occupiedSpots" />
        </div>

        <div class="flex flex-wrap gap-3 mb-6">
            @if (Route::has('panel.parking.entry.create'))
                <a href="{{ route('panel.parking.entry.create') }}" class="btn-primary">Nova entrada</a>
            @endif
            @if (Route::has('panel.parking.exit.index'))
                <a href="{{ route('panel.parking.exit.index') }}" class="btn-secondary">Registrar saída</a>
            @endif
        </div>

        <x-card title="Dados do estacionamento">
            <form method="POST" action="{{ route('panel.parking.lot.store') }}">
                @csrf
                <x-form-field label="Nome" name="name" :value="old('name', $parkingLot->name)" required />
                <x-form-field label="Total de vagas" name="total_spots" type="number" :value="old('total_spots', $parkingLot->total_spots)" required />
                <button type="submit" class="btn-secondary">Salvar alterações</button>
            </form>
        </x-card>
    @endif
@endsection
