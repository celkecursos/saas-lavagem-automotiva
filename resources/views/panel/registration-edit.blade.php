@extends('layouts.car-wash-panel')

@section('title', 'Corrigir cadastro — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Corrigir cadastro</h1>

    <div class="alert-danger">
        Seu cadastro foi rejeitado. Motivo: <strong>{{ $carWash->rejection_reason }}</strong>
        — corrija os dados abaixo e reenvie para nova análise.
    </div>

    <x-card class="max-w-xl mt-4">
        <form method="POST" action="{{ route('panel.registration.update') }}">
            @csrf
            @method('PUT')

            <x-form-field label="Nome do lava-rápido" name="name" :value="old('name', $carWash->name)" required />
            <x-form-field label="CNPJ (ou CPF)" name="document" :value="old('document', $carWash->document)" required />
            <x-form-field label="Telefone" name="phone" :value="old('phone', $carWash->phone)" />
            <x-form-field label="E-mail do estabelecimento" name="email" type="email" :value="old('email', $carWash->email)" required />
            <x-form-field label="Endereço" name="address_line" :value="old('address_line', $carWash->address_line)" required />
            <div class="grid grid-cols-3 gap-3">
                <x-form-field label="Cidade" name="city" :value="old('city', $carWash->city)" required />
                <x-form-field label="UF" name="state" :value="old('state', $carWash->state)" maxlength="2" required />
                <x-form-field label="CEP" name="zip_code" :value="old('zip_code', $carWash->zip_code)" required />
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('panel.dashboard') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Reenviar para análise</button>
            </div>
        </form>
    </x-card>
@endsection
