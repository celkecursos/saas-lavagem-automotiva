@extends('layouts.admin')

@section('title', 'Novo plano de repasse — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Novo plano de repasse</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('payout-plans.store') }}">
            @csrf
            <x-form-field label="Categoria" name="category" :value="old('category')" placeholder="ex: Essencial, Turbo, Master" required />
            <x-form-field label="Nível" name="level" type="number" :value="old('level', 1)" required />
            <x-form-field label="Rótulo exibido" name="label" :value="old('label')" placeholder="ex: Essencial Nível 1" required />
            <x-form-field label="Valor base (centavos)" name="base_price_cents" type="number" :value="old('base_price_cents')" required />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', true))
                       class="rounded border-gray-300 dark:border-gray-700">
                Ativo (disponível pro lava-rápido escolher)
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('payout-plans.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
