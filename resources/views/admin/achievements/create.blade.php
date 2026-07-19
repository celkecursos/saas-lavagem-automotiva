@extends('layouts.admin')

@section('title', 'Nova conquista — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Nova conquista</h1>

    <div class="alert-warning max-w-xl">
        Criar uma conquista aqui só cadastra o catálogo — pra ela desbloquear
        sozinha, o code precisa de uma checagem correspondente no código
        (AchievementChecker).
    </div>

    <x-card class="max-w-xl mt-4">
        <form method="POST" action="{{ route('achievements.store') }}">
            @csrf
            <x-form-field label="Code" name="code" :value="old('code')" placeholder="ex: first_wash" required />
            <x-form-field label="Nome" name="name" :value="old('name')" placeholder="ex: Primeira Lavagem" required />
            <x-form-field label="Descrição" name="description" :value="old('description')" required />
            <x-form-field label="Ícone (emoji)" name="icon" :value="old('icon')" placeholder="🏆" required />
            <x-form-field label="Pontos concedidos" name="points_reward" type="number" :value="old('points_reward', 0)" required />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', true))
                       class="rounded border-gray-300 dark:border-gray-700">
                Ativa
            </label>

            <div class="flex justify-end gap-2">
                <a href="{{ route('achievements.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
