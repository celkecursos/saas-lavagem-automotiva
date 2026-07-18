@extends('layouts.car-wash-panel')

@section('title', 'Meus produtos — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Meus produtos</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Clube de lavagem: escolhe payout_plan e aguarda aprovação do
             admin (task-5, seção 5) — formulário no commit seguinte. --}}
        <x-card title="Clube de lavagem">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Receba assinantes do clube e ganhe um repasse fixo por
                lavagem confirmada, conforme o plano de repasse escolhido.
            </p>

            @if ($clube)
                <p class="text-sm mb-3">
                    Situação: <x-badge :status="$clube->status" />
                    @if ($clube->payoutPlan)
                        <span class="text-gray-500 dark:text-gray-400">— plano {{ $clube->payoutPlan->label }}</span>
                    @endif
                </p>
            @else
                <p class="text-sm mb-3">Situação: <x-badge status="nao-contratado" variant="secondary">não contratado</x-badge></p>
            @endif

            @if ($isOwner)
                @if ($clube?->status === 'active')
                    <x-confirm-modal :action="route('panel.products.club.pause')"
                                     title="Pausar o clube de lavagem?"
                                     message="Reativar depois exige nova aprovação da plataforma."
                                     confirm-label="Pausar">
                        <x-slot:trigger><button type="button" class="btn-secondary">Pausar</button></x-slot:trigger>
                    </x-confirm-modal>
                @elseif ($clube?->status === 'pending')
                    <p class="text-sm text-gray-500 dark:text-gray-400">Solicitação aguardando aprovação da plataforma.</p>
                @else
                    {{-- Escolha do payout_plan: só opções do catálogo do
                         admin, nunca valor livre (task-5, seção 5). --}}
                    <form method="POST" action="{{ route('panel.products.club.request') }}">
                        @csrf
                        <x-form-field label="Plano de repasse" name="payout_plan_id">
                            <select name="payout_plan_id" id="payout_plan_id" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                                <option value="">Escolha um plano…</option>
                                @foreach ($payoutPlans as $payoutPlan)
                                    <option value="{{ $payoutPlan->id }}" @selected(old('payout_plan_id') == $payoutPlan->id)>
                                        {{ $payoutPlan->label }} — R$ {{ number_format($payoutPlan->base_price_cents / 100, 2, ',', '.') }} por lavagem
                                    </option>
                                @endforeach
                            </select>
                        </x-form-field>
                        <button type="submit" class="btn-primary">
                            {{ $clube ? 'Solicitar novamente' : 'Solicitar ativação' }}
                        </button>
                    </form>
                @endif
            @endif
        </x-card>

        {{-- Estacionamento: 100% self-service (task-5, seção 5). --}}
        <x-card title="Gerenciador de estacionamento">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Controle de entrada/saída, tarifas e cobrança por período —
                independente do clube de lavagem.
            </p>

            <p class="text-sm mb-3">
                Situação:
                @if ($estacionamento)
                    <x-badge :status="$estacionamento->status" />
                @else
                    <x-badge status="nao-contratado" variant="secondary">não contratado</x-badge>
                @endif
            </p>

            @if ($isOwner)
                @if ($estacionamento?->status === 'active')
                    <x-confirm-modal :action="route('panel.products.parking.pause')"
                                     title="Pausar o estacionamento?"
                                     message="Você pode reativar quando quiser, sem nova aprovação."
                                     confirm-label="Pausar">
                        <x-slot:trigger><button type="button" class="btn-secondary">Pausar</button></x-slot:trigger>
                    </x-confirm-modal>
                @else
                    <form method="POST" action="{{ route('panel.products.parking.activate') }}">
                        @csrf
                        <button type="submit" class="btn-primary">
                            {{ $estacionamento ? 'Reativar' : 'Ativar' }}
                        </button>
                    </form>
                @endif
            @endif
        </x-card>
    </div>
@endsection
