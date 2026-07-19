@extends('layouts.admin')

@section('title', 'Planos de repasse — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Planos de repasse</h1>
        @can('payout-plans.create')
            <a href="{{ route('payout-plans.create') }}" class="btn-primary">Novo plano</a>
        @endcan
    </div>

    <x-data-table :rows="$payoutPlans" empty-message="Nenhum plano de repasse cadastrado">
        <x-slot:head>
            <x-data-table.th>Rótulo</x-data-table.th>
            <x-data-table.th>Categoria / Nível</x-data-table.th>
            <x-data-table.th>Valor base</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($payoutPlans as $payoutPlan)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $payoutPlan->label }}</td>
                <td class="px-4 py-3">{{ $payoutPlan->category }} · Nível {{ $payoutPlan->level }}</td>
                <td class="px-4 py-3">R$ {{ number_format($payoutPlan->base_price_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3">
                    @if ($payoutPlan->active)
                        <x-badge status="active" />
                    @else
                        <x-badge status="inactive" variant="secondary">inativo</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @can('payout-plans.edit')
                        <a href="{{ route('payout-plans.edit', $payoutPlan) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
