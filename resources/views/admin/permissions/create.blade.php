@extends('layouts.admin')

@section('title', 'Nova permission — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Nova permission</h1>

    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <x-form-field label="Nome (recurso.acao)" name="name" :value="old('name')" placeholder="ex: relatorios.exportar" required />
            <div class="flex justify-end gap-2">
                <a href="{{ route('permissions.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Salvar</button>
            </div>
        </form>
    </x-card>
@endsection
