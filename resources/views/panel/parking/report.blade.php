@extends('layouts.car-wash-panel')

@section('title', 'Relatório do estacionamento — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Relatório do estacionamento</h1>

    <form method="GET" action="{{ route('panel.parking.report') }}" class="flex items-end gap-3 mb-6">
        <x-form-field label="Início" name="inicio" type="date" :value="$periodStart->format('Y-m-d')" />
        <x-form-field label="Fim" name="fim" type="date" :value="$periodEnd->format('Y-m-d')" />
        <button type="submit" class="btn-primary">Filtrar</button>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-tile label="Faturamento no período" :value="'R$ '.number_format($totalRevenueCents / 100, 2, ',', '.')" />
        <x-stat-tile label="Veículos atendidos" :value="$vehiclesServed" />
        <x-stat-tile label="Taxa de ocupação média" :value="number_format($occupancyRate, 1, ',', '.').'%'" />
        <x-stat-tile label="Monetização do período atual"
            :value="$latestCharge === null ? 'Sem cobrança gerada' : ($latestCharge->is_free ? 'Gratuito' : $latestCharge->fee_percentage_applied.'%')" />
    </div>
@endsection
