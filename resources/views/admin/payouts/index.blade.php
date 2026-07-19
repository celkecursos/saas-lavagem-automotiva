@extends('layouts.admin')

@section('title', 'Repasses — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Repasses</h1>

        <form method="GET" action="{{ route('payouts.index') }}">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                <option value="">Todos</option>
                @foreach (['pending' => 'Pendentes', 'paid' => 'Pagos', 'failed' => 'Falharam'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$payouts" empty-message="Nenhum repasse gerado ainda">
        <x-slot:head>
            <x-data-table.th>Lava-rápido</x-data-table.th>
            <x-data-table.th>Período</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
        </x-slot:head>

        @foreach ($payouts as $payout)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('payouts.show', $payout) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $payout->carWash->name }}
                    </a>
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                    {{ $payout->period_start->format('d/m/Y') }} – {{ $payout->period_end->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3">R$ {{ number_format($payout->total_amount_cents / 100, 2, ',', '.') }}</td>
                <td class="px-4 py-3"><x-badge :status="$payout->status" /></td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
