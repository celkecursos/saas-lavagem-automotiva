@extends('layouts.admin')

@section('title', 'Novo papel — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Novo papel</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <x-form-field label="Nome" name="name" :value="old('name')" required />
            <div class="flex justify-end gap-2">
                <a href="{{ route('roles.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
