@extends('layouts.admin')

@section('title', 'Nova recompensa de fidelidade — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Nova recompensa de fidelidade</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('loyalty-redemptions.store') }}" x-data="{ rewardType: '{{ old('reward_type', 'free_wash') }}' }">
            @csrf
            <x-form-field label="Nome" name="name" :value="old('name')" placeholder="ex: 1 lavagem grátis" required />
            <x-form-field label="Custo em pontos" name="points_cost" type="number" :value="old('points_cost')" required />

            <x-form-field label="Tipo de recompensa" name="reward_type">
                <select name="reward_type" id="reward_type" x-model="rewardType" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                    <option value="free_wash" @selected(old('reward_type') === 'free_wash')>Lavagem grátis</option>
                    <option value="discount_next_renewal" @selected(old('reward_type') === 'discount_next_renewal')>Desconto na próxima renovação</option>
                </select>
            </x-form-field>

            <div x-show="rewardType === 'discount_next_renewal'">
                <x-form-field label="Percentual de desconto" name="discount_percent" type="number" step="0.01" :value="old('discount_percent')" />
            </div>

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', true))
                       class="rounded border-gray-300 dark:border-gray-700">
                Ativa (disponível na loja)
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('loyalty-redemptions.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
