@extends('layouts.car-wash-panel')

@section('title', 'Confirmar lavagem — Painel')

@section('content')
    <div class="max-w-md mx-auto">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Confirmar lavagem</h1>

        <x-card>
            <form method="POST" action="{{ route('panel.washes.confirm.store') }}">
                @csrf
                <x-form-field label="Código de confirmação" name="confirmation_code"
                              inputmode="numeric" maxlength="6" autofocus />
                <button type="submit" class="btn-primary w-full">Confirmar</button>
            </form>
        </x-card>
    </div>
@endsection
