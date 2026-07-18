@extends('layouts.admin')

@section('title', 'Editar gateway — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
        Editar gateway — {{ $gateway->label ?: $gateway->type->name }}
    </h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('payment-gateways.update', $gateway) }}">
            @csrf
            @method('PUT')

            <x-form-field label="Rótulo (opcional)" name="label" :value="old('label', $gateway->label)" />

            {{-- Segredo nunca é pré-preenchido; em branco mantém o atual.
                 A chave pública do encryptCard não é cadastrada — é obtida
                 via API com o próprio token (task-4, seção 5.3). --}}
            <x-form-field label="Token da API (deixe em branco pra manter o atual)"
                          name="credentials.token" type="password" autocomplete="off" />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="sandbox_mode" value="0">
                <input type="checkbox" name="sandbox_mode" value="1" @checked(old('sandbox_mode', $gateway->sandbox_mode))
                       class="rounded border-gray-300 dark:border-gray-700">
                Ambiente sandbox (testes)
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('payment-gateways.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
