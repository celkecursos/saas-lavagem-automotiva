@extends('layouts.admin')

@section('title', 'Editar plano de repasse — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar plano de repasse</h1>

    <div class="alert-warning max-w-xl">
        Mudar o valor base afeta o cálculo de repasse de todos os
        lava-rápidos que escolheram este plano.
    </div>

    <x-card class="max-w-xl mt-4">
        <form method="POST" action="{{ route('payout-plans.update', $payoutPlan) }}">
            @csrf
            @method('PUT')
            <x-form-field label="Categoria" name="category" :value="old('category', $payoutPlan->category)" required />
            <x-form-field label="Nível" name="level" type="number" :value="old('level', $payoutPlan->level)" required />
            <x-form-field label="Rótulo exibido" name="label" :value="old('label', $payoutPlan->label)" required />
            <x-form-field label="Valor base (centavos)" name="base_price_cents" type="number" :value="old('base_price_cents', $payoutPlan->base_price_cents)" required />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $payoutPlan->active))
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
