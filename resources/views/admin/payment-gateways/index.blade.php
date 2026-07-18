@extends('layouts.admin')

@section('title', 'Gateways de pagamento — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Gateways de pagamento</h1>
        @can('payment-gateways.create')
            <a href="{{ route('payment-gateways.create') }}" class="btn-primary">Novo gateway</a>
        @endcan
    </div>

    <x-data-table :rows="$gateways" empty-message="Nenhum gateway configurado ainda">
        <x-slot:head>
            <x-data-table.th>Gateway</x-data-table.th>
            <x-data-table.th>Ambiente</x-data-table.th>
            <x-data-table.th>Situação</x-data-table.th>
            <x-data-table.th>Ações</x-data-table.th>
        </x-slot:head>

        @foreach ($gateways as $gateway)
            <tr>
                <td class="px-4 py-3">
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $gateway->label ?: $gateway->type->name }}</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $gateway->type->name }}</span>
                </td>
                <td class="px-4 py-3">
                    @if ($gateway->sandbox_mode)
                        <x-badge status="sandbox" variant="warning">sandbox</x-badge>
                    @else
                        <x-badge status="producao" variant="primary">produção</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @if ($gateway->is_active)
                        <x-badge status="active">ativo</x-badge>
                    @else
                        <x-badge status="inactive" variant="secondary">inativo</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @can('payment-gateways.edit')
                            <a href="{{ route('payment-gateways.edit', $gateway) }}"
                               class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                        @endcan
                        @if (! $gateway->is_active)
                            @can('payment-gateways.activate')
                                <x-confirm-modal :action="route('payment-gateways.activate', $gateway)"
                                                 title="Ativar este gateway?"
                                                 message="Os demais gateways serão desativados — todos os próximos checkouts passam a usar este."
                                                 confirm-label="Ativar">
                                    <x-slot:trigger>
                                        <button type="button" class="text-sm text-green-600 dark:text-green-400 hover:underline cursor-pointer">Ativar</button>
                                    </x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
