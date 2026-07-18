@extends('layouts.public')

@section('title', 'Cadastre seu lava-rápido — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Cadastre seu lava-rápido</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Preencha os dados abaixo. Seu cadastro passa por uma análise da
            plataforma antes de aparecer para os assinantes.
        </p>

        <form method="POST" action="{{ route('partners.register.store') }}">
            @csrf

            <x-card title="Dados do responsável" class="mb-6">
                <x-form-field label="Nome completo" name="owner_name" :value="old('owner_name')" required />
                <x-form-field label="E-mail" name="owner_email" type="email" :value="old('owner_email')" required />
                <x-form-field label="Telefone" name="owner_phone" :value="old('owner_phone')" required />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-form-field label="Senha" name="password" type="password" required />
                    <x-form-field label="Confirmar senha" name="password_confirmation" type="password" required />
                </div>
            </x-card>

            <x-card title="Dados do estabelecimento" class="mb-6">
                <x-form-field label="Nome do lava-rápido" name="car_wash_name" :value="old('car_wash_name')" required />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <x-form-field label="CNPJ (ou CPF, se MEI/autônomo)" name="document" :value="old('document')" required />
                    <x-form-field label="Telefone do estabelecimento" name="car_wash_phone" :value="old('car_wash_phone')" />
                </div>
                <x-form-field label="E-mail do estabelecimento" name="car_wash_email" type="email" :value="old('car_wash_email')" required />
                <x-form-field label="Endereço" name="address_line" :value="old('address_line')" required />
                <div class="grid grid-cols-3 gap-3">
                    <x-form-field label="Cidade" name="city" :value="old('city')" required />
                    <x-form-field label="UF" name="state" :value="old('state')" maxlength="2" required />
                    <x-form-field label="CEP" name="zip_code" :value="old('zip_code')" required />
                </div>
            </x-card>

            <button type="submit" class="btn-primary w-full">Enviar cadastro</button>
        </form>
    </div>
@endsection
