@extends('layouts.admin')

@section('title', 'Editar plano — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar plano — {{ $plan->name }}</h1>

    <div class="alert-warning max-w-xl">
        Alterar preço ou cota não afeta assinantes já ativos no ciclo atual —
        a mudança só entra em vigor na próxima renovação de cada um.
    </div>

    <x-card class="max-w-xl mt-4">
        <form method="POST" action="{{ route('payment-plans.update', $plan) }}">
            @csrf
            @method('PUT')
            <x-form-field label="Nome" name="name" :value="old('name', $plan->name)" required />
            <x-form-field label="Preço (centavos)" name="price_cents" type="number" :value="old('price_cents', $plan->price_cents)" required />
            <x-form-field label="Cota de lavagens por ciclo" name="wash_quota" type="number" :value="old('wash_quota', $plan->wash_quota)" required />

            <x-form-field label="Periodicidade" name="quota_period">
                <select name="quota_period" id="quota_period"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                    @foreach (['monthly' => 'Mensal', 'weekly' => 'Semanal', 'yearly' => 'Anual'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('quota_period', $plan->quota_period) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-form-field>

            <x-form-field label="Limite de lavagens por dia no mesmo lava-rápido (opcional)"
                          name="max_redemptions_per_day_per_car_wash" type="number"
                          :value="old('max_redemptions_per_day_per_car_wash', $plan->max_redemptions_per_day_per_car_wash)" />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="rollover_quota" value="0">
                <input type="checkbox" name="rollover_quota" value="1" @checked(old('rollover_quota', $plan->rollover_quota))
                       class="rounded border-gray-300 dark:border-gray-700">
                Cota não usada acumula pro próximo ciclo
            </label>

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $plan->active))
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
