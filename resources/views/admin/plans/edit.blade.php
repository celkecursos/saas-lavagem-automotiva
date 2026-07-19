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

    {{-- Vantagens de marketing (plan_features) — gerenciadas aqui dentro,
         não numa tela separada (task-11, seção 4). --}}
    <x-card title="Vantagens exibidas na vitrine" class="max-w-xl mt-6">
        @if ($plan->features->isEmpty())
            <x-empty-state message="Nenhuma vantagem cadastrada ainda." />
        @else
            <ul class="space-y-2 mb-4">
                @foreach ($plan->features as $feature)
                    <li class="flex items-center justify-between gap-2 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="flex flex-col">
                                <form method="POST" action="{{ route('payment-plans.features.move', [$plan, $feature]) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer leading-none" @disabled($loop->first)>▲</button>
                                </form>
                                <form method="POST" action="{{ route('payment-plans.features.move', [$plan, $feature]) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer leading-none" @disabled($loop->last)>▼</button>
                                </form>
                            </div>
                            <span class="{{ $feature->active ? '' : 'line-through text-gray-400' }}">{{ $feature->label }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('payment-plans.features.update', [$plan, $feature]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="label" value="{{ $feature->label }}">
                                <input type="hidden" name="active" value="{{ $feature->active ? 0 : 1 }}">
                                <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                    {{ $feature->active ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('payment-plans.features.destroy', [$plan, $feature]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline cursor-pointer">Remover</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('payment-plans.features.store', $plan) }}" class="flex items-end gap-2">
            @csrf
            <div class="flex-1">
                <x-form-field label="Nova vantagem" name="label" placeholder="ex: Suporte prioritário" />
            </div>
            <button type="submit" class="btn-secondary mb-4">Adicionar</button>
        </form>
    </x-card>
@endsection
