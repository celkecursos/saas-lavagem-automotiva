@extends('layouts.car-wash-panel')

@section('title', 'Dashboard — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $carWash->name }}</h1>

    @if ($carWash->status !== 'approved')
        {{-- Cadastro não aprovado: SÓ o banner de status (task-14, seção 5). --}}
        @if ($carWash->status === 'pending')
            <div class="alert-warning">
                Seu cadastro está <strong>em análise</strong>. Você será avisado
                assim que a equipe da plataforma aprovar.
            </div>
        @elseif ($carWash->status === 'rejected')
            <div class="alert-danger">
                Seu cadastro foi <strong>rejeitado</strong>.
                @if ($carWash->rejection_reason)
                    Motivo: {{ $carWash->rejection_reason }}
                @endif
            </div>
            @if (Route::has('panel.registration.edit'))
                <a href="{{ route('panel.registration.edit') }}" class="btn-primary">Corrigir e reenviar cadastro</a>
            @endif
        @elseif ($carWash->status === 'suspended')
            <div class="alert-danger">
                Seu cadastro está <strong>suspenso</strong>. Entre em contato com
                o suporte da plataforma.
            </div>
        @endif
    @else
        {{-- Atalho em destaque: ação mais frequente do balcão (task-14, seção 5). --}}
        @if (in_array('clube_lavagem', $activeProducts, true) && Route::has('panel.washes.confirm'))
            <a href="{{ route('panel.washes.confirm') }}" class="btn-primary text-base px-6 py-3 mb-6 inline-flex">
                Confirmar lavagem
            </a>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @if (isset($summaries['clube_lavagem']))
                <x-card title="Clube de lavagem">
                    <div class="grid grid-cols-2 gap-4">
                        <x-stat-tile label="Lavagens confirmadas este mês"
                                     :value="$summaries['clube_lavagem']['washes_this_month']" />
                        <x-stat-tile label="Repasse pendente"
                                     :value="'R$ '.number_format($summaries['clube_lavagem']['pending_payout_cents'] / 100, 2, ',', '.')" />
                    </div>
                </x-card>
            @endif

            @if (isset($summaries['estacionamento']))
                <x-card title="Estacionamento">
                    <div class="grid grid-cols-2 gap-4">
                        <x-stat-tile label="Vagas livres agora"
                                     :value="$summaries['estacionamento']['free_spots']" />
                        <x-stat-tile label="Faturamento do mês"
                                     :value="'R$ '.number_format($summaries['estacionamento']['revenue_this_month_cents'] / 100, 2, ',', '.')" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                        Cobrança do período:
                        @if ($summaries['estacionamento']['latest_charge'] === null)
                            <x-badge status="free" variant="secondary">ainda sem apuração</x-badge>
                        @elseif ($summaries['estacionamento']['latest_charge']->is_free)
                            <x-badge status="free">grátis</x-badge>
                        @else
                            <x-badge :status="$summaries['estacionamento']['latest_charge']->status">
                                {{ number_format($summaries['estacionamento']['latest_charge']->fee_percentage_applied, 2, ',', '.') }}%
                            </x-badge>
                        @endif
                    </p>
                </x-card>
            @endif

            @if (empty($activeProducts))
                <x-card>
                    <x-empty-state message="Nenhum produto ativo ainda — ative o clube de lavagem ou o estacionamento em Meus produtos." />
                </x-card>
            @endif
        </div>
    @endif
@endsection
