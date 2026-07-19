@extends('layouts.admin')

@section('title', 'Recompensas de fidelidade — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Recompensas de fidelidade</h1>
        @can('loyalty-redemptions.create')
            <a href="{{ route('loyalty-redemptions.create') }}" class="btn-primary">Nova recompensa</a>
        @endcan
    </div>

    <x-data-table :rows="$loyaltyRedemptions" empty-message="Nenhuma recompensa cadastrada">
        <x-slot:head>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th>Tipo</x-data-table.th>
            <x-data-table.th>Pontos</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($loyaltyRedemptions as $loyaltyRedemption)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $loyaltyRedemption->name }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                    {{ $loyaltyRedemption->reward_type === 'free_wash' ? 'Lavagem grátis' : 'Desconto na renovação ('.$loyaltyRedemption->discount_percent.'%)' }}
                </td>
                <td class="px-4 py-3">{{ $loyaltyRedemption->points_cost }}</td>
                <td class="px-4 py-3">
                    @if ($loyaltyRedemption->active)
                        <x-badge status="active" />
                    @else
                        <x-badge status="inactive" variant="secondary">inativo</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @can('loyalty-redemptions.edit')
                        <a href="{{ route('loyalty-redemptions.edit', $loyaltyRedemption) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
