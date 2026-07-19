@extends('layouts.admin')

@section('title', 'Editar conquista — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar conquista</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('achievements.update', $achievement) }}">
            @csrf
            @method('PUT')
            <x-form-field label="Code" name="code" :value="old('code', $achievement->code)" required />
            <x-form-field label="Nome" name="name" :value="old('name', $achievement->name)" required />
            <x-form-field label="Descrição" name="description" :value="old('description', $achievement->description)" required />
            <x-form-field label="Ícone (emoji)" name="icon" :value="old('icon', $achievement->icon)" required />
            <x-form-field label="Pontos concedidos" name="points_reward" type="number" :value="old('points_reward', $achievement->points_reward)" required />

            <label class="flex items-center gap-2 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $achievement->active))
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
