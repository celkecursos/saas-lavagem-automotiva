@extends('layouts.public')

@section('title', 'Aceitar convite — Celke Wash Club')

@section('content')
    <div class="max-w-md mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Convite de equipe</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            Você foi convidado pra equipe do
            <strong>{{ $invitation->carWash->name }}</strong>. Complete seu
            cadastro pra aceitar ({{ $invitation->email }}).
        </p>

        <x-card>
            <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                @csrf
                <x-form-field label="Seu nome" name="name" :value="old('name')" required />
                <x-form-field label="Senha" name="password" type="password" required />
                <x-form-field label="Confirmar senha" name="password_confirmation" type="password" required />
                <button type="submit" class="btn-primary w-full">Aceitar convite</button>
            </form>
        </x-card>
    </div>
@endsection
