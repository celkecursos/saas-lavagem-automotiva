{{-- Cadastro do assinante no mesmo shell das telas de auth (task-6.1),
     em vez do layout público — o fluxo entrar/cadastrar/recuperar senha
     fica visualmente coeso. --}}
<x-guest-layout title="Criar conta — Celke Wash Club">
    <x-slot:heading>Criar sua conta</x-slot:heading>
    <x-slot:subheading>Leva menos de um minuto. Depois é só escolher o plano.</x-slot:subheading>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <x-form-field label="Nome completo" name="name" :value="old('name')" required autofocus />
        <x-form-field label="E-mail" name="email" type="email" :value="old('email')" required />
        <x-form-field label="Telefone" name="phone" :value="old('phone')" required />
        <x-form-field label="CPF (opcional)" name="cpf" :value="old('cpf')" />
        <x-form-field label="Código de indicação (opcional)" name="referral_code"
                      :value="old('referral_code', $referralCode ?? null)" />
        <x-form-field label="Senha" name="password" type="password" required />
        <x-form-field label="Confirmar senha" name="password_confirmation" type="password" required />

        <button type="submit"
                class="mt-2 w-full rounded-lg bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
            Criar conta
        </button>
    </form>

    @if (Route::has('login'))
        <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
            Já tem conta?
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Entrar</a>
        </p>
    @endif
</x-guest-layout>
