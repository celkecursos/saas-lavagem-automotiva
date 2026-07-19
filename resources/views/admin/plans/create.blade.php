@extends('layouts.admin')

@section('title', 'Novo plano — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Novo plano</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('payment-plans.store') }}">
            @csrf
            <x-form-field label="Nome" name="name" :value="old('name')" required />
            <x-form-field label="Preço (centavos)" name="price_cents" type="number" :value="old('price_cents')" required />
            <x-form-field label="Cota de lavagens por ciclo" name="wash_quota" type="number" :value="old('wash_quota')" required />

            <x-form-field label="Periodicidade" name="quota_period">
                <select name="quota_period" id="quota_period"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                    <option value="monthly" selected>Mensal</option>
                    <option value="weekly">Semanal</option>
                    <option value="yearly">Anual</option>
                </select>
            </x-form-field>

            <x-form-field label="Limite de lavagens por dia no mesmo lava-rápido (opcional)"
                          name="max_redemptions_per_day_per_car_wash" type="number" :value="old('max_redemptions_per_day_per_car_wash')" />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="rollover_quota" value="0">
                <input type="checkbox" name="rollover_quota" value="1" @checked(old('rollover_quota'))
                       class="rounded border-gray-300 dark:border-gray-700">
                Cota não usada acumula pro próximo ciclo
            </label>

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', true))
                       class="rounded border-gray-300 dark:border-gray-700">
                Ativo (aparece na vitrine)
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('payment-plans.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
