@extends('layouts.car-wash-panel')

@section('title', 'Saída — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Saída de veículo</h1>

    <form method="GET" action="{{ route('panel.parking.exit.index') }}" class="mb-4">
        <x-form-field label="Buscar por placa" name="plate" :value="request('plate')" />
        <button type="submit" class="btn-secondary">Buscar</button>
    </form>

    <x-data-table :rows="$sessions" empty-message="Nenhum veículo estacionado no momento">
        <x-slot:head>
            <x-data-table.th>Placa</x-data-table.th>
            <x-data-table.th>Entrada</x-data-table.th>
            <x-data-table.th>Tarifa</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($sessions as $session)
            <tr>
                <td class="px-4 py-3 font-mono">{{ $session->plate }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $session->entry_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">{{ $session->parkingRate->name }}</td>
                <td class="px-4 py-3">
                    <x-confirm-modal :action="route('panel.parking.exit.store', $session)"
                                     title="Registrar saída deste veículo?"
                                     message="O valor é calculado automaticamente pela tarifa."
                                     confirm-label="Confirmar saída">
                        <x-slot:trigger><button type="button" class="btn-primary">Registrar saída</button></x-slot:trigger>
                        <x-form-field label="Forma de pagamento" name="payment_method">
                            <select name="payment_method" id="payment_method"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                                <option value="cash">Dinheiro</option>
                                <option value="card">Cartão</option>
                                <option value="pix">Pix</option>
                            </select>
                        </x-form-field>
                    </x-confirm-modal>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
