@extends('layouts.admin')

@section('title', 'Configurações do estacionamento — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações do estacionamento</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('parking-billing-settings.update') }}">
            @csrf
            @method('PUT')
            <x-form-field label="Percentual cobrado quando não-gratuito (%)" name="fee_percentage"
                          type="number" step="0.01" :value="old('fee_percentage', $settings->fee_percentage)" required />
            <x-form-field label="Giros máximos por vaga por dia (antifraude)" name="max_turns_per_day_per_spot"
                          type="number" :value="old('max_turns_per_day_per_spot', $settings->max_turns_per_day_per_spot)" required />
            <button type="submit" class="btn-primary">Salvar</button>
        </form>
    </x-card>
@endsection
