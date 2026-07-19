@extends('layouts.public')

@section('title', 'Editar veículo — Celke Wash Club')

@section('content')
    <div class="max-w-md mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Editar veículo</h1>

        <x-card>
            <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
                @csrf
                @method('PUT')
                <x-form-field label="Placa" name="plate" :value="old('plate', $vehicle->plate)" required />
                <x-form-field label="Marca (opcional)" name="brand" :value="old('brand', $vehicle->brand)" />
                <x-form-field label="Modelo (opcional)" name="model" :value="old('model', $vehicle->model)" />
                <x-form-field label="Cor (opcional)" name="color" :value="old('color', $vehicle->color)" />
                <div class="flex justify-end gap-2">
                    <a href="{{ route('vehicles.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
