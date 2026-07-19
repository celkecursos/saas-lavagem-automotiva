@extends('layouts.admin')

@section('title', 'Editar permission — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Editar permission</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('permissions.update', $permission) }}">
            @csrf
            @method('PUT')
            <x-form-field label="Nome" name="name" :value="old('name', $permission->name)" required />
            <div class="flex justify-end gap-2">
                <a href="{{ route('permissions.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
