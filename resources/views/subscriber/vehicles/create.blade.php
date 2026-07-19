@extends('layouts.public')

@section('title', 'Adicionar veículo — Celke Wash Club')

@section('content')
    <div class="max-w-md mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Adicionar veículo</h1>

        <x-card>
            <form method="POST" action="{{ route('vehicles.store') }}">
                @csrf
                <x-form-field label="Placa" name="plate" :value="old('plate')" placeholder="ABC1234 ou ABC1D23" required />
                <x-form-field label="Marca (opcional)" name="brand" :value="old('brand')" />
                <x-form-field label="Modelo (opcional)" name="model" :value="old('model')" />
                <x-form-field label="Cor (opcional)" name="color" :value="old('color')" />
                <button type="submit" class="btn-primary w-full">Cadastrar</button>
            </form>
        </x-card>
    </div>
@endsection
