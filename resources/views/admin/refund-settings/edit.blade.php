@extends('layouts.admin')

@section('title', 'Configurações de reembolso — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações de reembolso</h1>

    <x-card class="max-w-xl">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Os 7 dias iniciais de arrependimento (CDC art. 49) são fixos e sempre
            self-service. Depois disso, o self-service só continua disponível se
            a janela estendida abaixo estiver ligada.
        </p>

        <form method="POST" action="{{ route('refund-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" name="extended_self_service_enabled" id="extended_self_service_enabled" value="1"
                       @checked(old('extended_self_service_enabled', $settings->extended_self_service_enabled))
                       class="rounded border-gray-300 dark:border-gray-700">
                <label for="extended_self_service_enabled" class="text-sm text-gray-700 dark:text-gray-300">
                    Habilitar janela estendida de self-service
                </label>
            </div>

            <x-form-field label="Até quantos dias (a partir do pagamento)" name="extended_self_service_until_days"
                          type="number" :value="old('extended_self_service_until_days', $settings->extended_self_service_until_days)" />

            <button type="submit" class="btn-primary">Salvar</button>
        </form>
    </x-card>
@endsection
