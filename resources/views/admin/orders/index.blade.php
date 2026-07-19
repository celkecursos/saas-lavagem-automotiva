@extends('layouts.admin')

@section('title', 'Pedidos — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Pedidos</h1>

        <form method="GET" action="{{ route('orders.index') }}">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                <option value="">Todos os status</option>
                @foreach (['pending', 'paid', 'failed', 'refunded', 'chargeback', 'canceled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$orders" empty-message="Nenhum pedido ainda">
        <x-slot:head>
            <x-data-table.th>Usuário</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th>Gateway</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Data</x-data-table.th>
        </x-slot:head>

        @foreach ($orders as $order)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('orders.show', $order) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $order->user->name }}
                    </a>
                </td>
                <td class="px-4 py-3">R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $order->paymentGateway?->label ?? $order->paymentGateway?->type?->name ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge :status="$order->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
