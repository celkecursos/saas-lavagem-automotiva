@extends('layouts.public')

@section('title', 'Criar conta — Celke Wash Club')

@section('content')
    <div class="max-w-md mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Criar conta</h1>

        <x-card>
            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <x-form-field label="Nome completo" name="name" :value="old('name')" required />
                <x-form-field label="E-mail" name="email" type="email" :value="old('email')" required />
                <x-form-field label="Telefone" name="phone" :value="old('phone')" required />
                <x-form-field label="CPF (opcional)" name="cpf" :value="old('cpf')" />
                <x-form-field label="Código de indicação (opcional)" name="referral_code"
                              :value="old('referral_code', $referralCode ?? null)" />
                <x-form-field label="Senha" name="password" type="password" required />
                <x-form-field label="Confirmar senha" name="password_confirmation" type="password" required />
                <button type="submit" class="btn-primary w-full">Criar conta</button>
            </form>
        </x-card>

        @if (Route::has('login'))
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4 text-center">
                Já tem conta? <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Entrar</a>
            </p>
        @endif
    </div>
@endsection
