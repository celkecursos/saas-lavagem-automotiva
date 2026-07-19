@extends('layouts.car-wash-panel')

@section('title', 'Nova entrada — Painel')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Nova entrada</h1>

        @if ($parkingLot === null)
            <div class="alert-warning">Cadastre o estacionamento antes de operar entradas.</div>
        @else
            <x-card>
                <form method="POST" action="{{ route('panel.parking.entry.store') }}">
                    @csrf
                    <x-form-field label="Placa" name="plate" placeholder="ABC1234 ou ABC1D23" autofocus required />

                    @if ($rates->count() > 1)
                        <x-form-field label="Tarifa" name="parking_rate_id">
                            <select name="parking_rate_id" id="parking_rate_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100">
                                @foreach ($rates as $rate)
                                    <option value="{{ $rate->id }}">{{ $rate->name }} — R$ {{ number_format($rate->price_cents / 100, 2, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </x-form-field>
                    @elseif ($rates->isEmpty())
                        <div class="alert-warning">Cadastre ao menos 1 tarifa ativa antes de operar entradas.</div>
                    @endif

                    <button type="submit" class="btn-primary w-full" @disabled($rates->isEmpty())>Registrar entrada</button>
                </form>
            </x-card>
        @endif
    </div>
@endsection
