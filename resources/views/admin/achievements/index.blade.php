@extends('layouts.admin')

@section('title', 'Conquistas — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Conquistas</h1>
        @can('achievements.create')
            <a href="{{ route('achievements.create') }}" class="btn-primary">Nova conquista</a>
        @endcan
    </div>

    <x-data-table :rows="$achievements" empty-message="Nenhuma conquista cadastrada">
        <x-slot:head>
            <x-data-table.th></x-data-table.th>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th>Code</x-data-table.th>
            <x-data-table.th>Pontos</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($achievements as $achievement)
            <tr>
                <td class="px-4 py-3 text-xl">{{ $achievement->icon }}</td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $achievement->name }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $achievement->code }}</td>
                <td class="px-4 py-3">{{ $achievement->points_reward }}</td>
                <td class="px-4 py-3">
                    @if ($achievement->active)
                        <x-badge status="active" />
                    @else
                        <x-badge status="inactive" variant="secondary">inativo</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    @can('achievements.edit')
                        <a href="{{ route('achievements.edit', $achievement) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
