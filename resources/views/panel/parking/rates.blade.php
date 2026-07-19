@extends('layouts.car-wash-panel')

@section('title', 'Tarifas — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Tarifas</h1>

    @if ($parkingLot === null)
        <div class="alert-warning">Cadastre o estacionamento antes de criar tarifas.</div>
    @else
        <x-data-table :rows="$rates" empty-message="Nenhuma tarifa cadastrada ainda" class="mb-6">
            <x-slot:head>
                <x-data-table.th>Nome</x-data-table.th>
                <x-data-table.th>Unidade</x-data-table.th>
                <x-data-table.th>Valor</x-data-table.th>
                <x-data-table.th>Tolerância</x-data-table.th>
                <x-data-table.th>Status</x-data-table.th>
            </x-slot:head>

            @foreach ($rates as $rate)
                <tr>
                    <td class="px-4 py-3">{{ $rate->name }}</td>
                    <td class="px-4 py-3">{{ ['hour' => 'Hora', 'day' => 'Diária', 'fraction' => 'Fração'][$rate->unit] }}</td>
                    <td class="px-4 py-3">R$ {{ number_format($rate->price_cents / 100, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ $rate->tolerance_minutes }} min</td>
                    <td class="px-4 py-3">
                        @if ($rate->active)
                            <x-badge status="active" />
                        @else
                            <x-badge status="inactive" variant="secondary">inativa</x-badge>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-data-table>

        <x-card title="Nova tarifa">
            <form method="POST" action="{{ route('panel.parking.rates.store') }}">
                @csrf
                <x-form-field label="Nome" name="name" placeholder="ex: Hora avulsa" required />
                <x-form-field label="Unidade" name="unit">
                    <select name="unit" id="unit" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                        <option value="hour">Hora</option>
                        <option value="day">Diária</option>
                        <option value="fraction">Fração</option>
                    </select>
                </x-form-field>
                <x-form-field label="Valor (centavos)" name="price_cents" type="number" required />
                <x-form-field label="Tolerância (minutos)" name="tolerance_minutes" type="number" :value="10" />
                <button type="submit" class="btn-primary">Adicionar</button>
            </form>
        </x-card>
    @endif
@endsection
