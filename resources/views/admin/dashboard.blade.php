@extends('layouts.admin')

@section('title', 'Dashboard — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Dashboard</h1>

    {{-- KPIs (task-11, seção 2). Valores monetários em centavos no banco. --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <x-stat-tile label="Assinantes ativos" :value="$kpis['active_subscribers']" />
        <x-stat-tile label="MRR aproximado" :value="'R$ '.number_format($kpis['mrr_cents'] / 100, 2, ',', '.')" />
        <x-stat-tile label="Lava-rápidos aprovados" :value="$kpis['car_washes_approved']" />
        <x-stat-tile label="Lava-rápidos pendentes" :value="$kpis['car_washes_pending']" />
        <x-stat-tile label="Lavagens no mês" :value="$kpis['washes_this_month']" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <x-stat-tile label="Repasses pendentes"
                     :value="$kpis['payouts_pending_count'].' (R$ '.number_format($kpis['payouts_pending_cents'] / 100, 2, ',', '.').')'" />
    </div>

    {{-- Atalhos pras filas com pendência (task-14, seção 4) — cada um só
         vira link quando a rota da fila existir (Route::has, mesma
         proteção da sidebar). --}}
    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mt-8 mb-3">Filas com pendência</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'Cadastros pendentes', 'count' => $queues['pending_registrations'], 'route' => 'car-washes.index', 'params' => ['status' => 'pending']],
            ['label' => 'Ativações do clube pendentes', 'count' => $queues['pending_club_activations'], 'route' => 'car-wash-product-subscriptions.index', 'params' => ['status' => 'pending']],
            ['label' => 'Cancelamentos pendentes', 'count' => $queues['pending_cancellations'], 'route' => 'cancellation-requests.index', 'params' => ['status' => 'pending']],
            ['label' => 'Cobranças sinalizadas', 'count' => $queues['flagged_parking_charges'], 'route' => 'parking-billing-charges.index', 'params' => ['flagged' => 1]],
        ] as $queue)
            <x-card>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $queue['count'] }}</p>
                @if (Route::has($queue['route']))
                    <a href="{{ route($queue['route'], $queue['params']) }}"
                       class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ $queue['label'] }}</a>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $queue['label'] }}</p>
                @endif
            </x-card>
        @endforeach
    </div>
@endsection
