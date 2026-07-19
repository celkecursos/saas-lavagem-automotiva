@extends('layouts.admin')

@section('title', 'Reembolsos pendentes de confirmação manual — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Reembolsos pendentes de confirmação manual</h1>

    <x-data-table :rows="$requests" empty-message="Nenhum reembolso aguardando confirmação manual">
        <x-slot:head>
            <x-data-table.th>Pedido</x-data-table.th>
            <x-data-table.th>Solicitado por</x-data-table.th>
            <x-data-table.th>Motivo</x-data-table.th>
            <x-data-table.th>Solicitado em</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($requests as $request)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('orders.show', $request->order) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        Pedido #{{ $request->order->id }}
                    </a>
                    <span class="text-gray-500 dark:text-gray-400">— {{ $request->order->user->name }}</span>
                </td>
                <td class="px-4 py-3">{{ $request->initiated_by === 'admin' ? 'Admin' : 'Assinante' }}</td>
                <td class="px-4 py-3">{{ $request->reason }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $request->requested_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">
                    <x-confirm-modal :action="route('order-refund-requests.mark-processed', $request)"
                                     title="Marcar como processado?"
                                     message="Confirma que o estorno já foi feito por fora (ex: painel do gateway)? O acesso do assinante será revogado."
                                     confirm-label="Marcar processado">
                        <x-slot:trigger><button type="button" class="btn-primary">Marcar processado</button></x-slot:trigger>
                    </x-confirm-modal>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
