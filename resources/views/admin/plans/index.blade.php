@extends('layouts.admin')

@section('title', 'Planos — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Planos</h1>
        @can('payment-plans.create')
            <a href="{{ route('payment-plans.create') }}" class="btn-primary">Novo plano</a>
        @endcan
    </div>

    <x-data-table :rows="$plans" empty-message="Nenhum plano cadastrado ainda">
        <x-slot:head>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th>Preço</x-data-table.th>
            <x-data-table.th>Cota</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Vantagens</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($plans as $plan)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $plan->name }}</td>
                <td class="px-4 py-3">R$ {{ number_format($plan->price_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3">{{ $plan->wash_quota }}/{{ $plan->quota_period === 'monthly' ? 'mês' : $plan->quota_period }}</td>
                <td class="px-4 py-3">
                    @if ($plan->active)
                        <x-badge status="active" />
                    @else
                        <x-badge status="inactive" variant="secondary">inativo</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">{{ $plan->features_count }}</td>
                <td class="px-4 py-3">
                    @can('payment-plans.edit')
                        <a href="{{ route('payment-plans.edit', $plan) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
