@extends('layouts.admin')

@section('title', 'Permissões — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Permissões</h1>
        @can('permissions.create')
            <a href="{{ route('permissions.create') }}" class="btn-primary">Nova permission</a>
        @endcan
    </div>

    <x-data-table :rows="$permissions" empty-message="Nenhuma permission cadastrada">
        <x-slot:head>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($permissions as $permission)
            <tr>
                <td class="px-4 py-3 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $permission->name }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        @can('permissions.edit')
                            <a href="{{ route('permissions.edit', $permission) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                        @endcan
                        @can('permissions.destroy')
                            <x-confirm-modal :action="route('permissions.destroy', $permission)" method="DELETE"
                                             title="Remover esta permission?"
                                             message="Papéis com esta permission perdem o acesso associado.">
                                <x-slot:trigger><button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline cursor-pointer">Remover</button></x-slot:trigger>
                            </x-confirm-modal>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
