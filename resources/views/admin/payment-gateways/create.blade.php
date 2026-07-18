@extends('layouts.admin')

@section('title', 'Novo gateway — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Novo gateway de pagamento</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('payment-gateways.store') }}">
            @csrf

            <x-form-field label="Provedor" name="payment_gateway_type_id">
                <select name="payment_gateway_type_id" id="payment_gateway_type_id"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-gray-900 dark:text-gray-100 text-sm">
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected(old('payment_gateway_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </x-form-field>

            <x-form-field label="Rótulo (opcional)" name="label" :value="old('label')"
                          placeholder="ex: PagSeguro - conta principal" />

            {{-- Única credencial: a chave pública do encryptCard é obtida
                 via API com esse mesmo token (task-4, seção 5.3). --}}
            <x-form-field label="Token da API" name="credentials.token" type="password" autocomplete="off" />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="sandbox_mode" value="0">
                <input type="checkbox" name="sandbox_mode" value="1" @checked(old('sandbox_mode', true))
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
