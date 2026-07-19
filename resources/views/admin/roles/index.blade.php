@extends('layouts.admin')

@section('title', 'Papéis — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Papéis</h1>
        @can('roles.create')
            <a href="{{ route('roles.create') }}" class="btn-primary">Novo papel</a>
        @endcan
    </div>

    <x-data-table :rows="$roles" empty-message="Nenhum papel cadastrado">
        <x-slot:head>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th>Ordem</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($roles as $role)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</td>
                <td class="px-4 py-3">{{ $role->order }}</td>
                <td class="px-4 py-3">
                    @if ($role->name !== 'Super Admin')
                        <div class="flex items-center gap-3">
                            @can('role-permissions.index')
                                <a href="{{ route('role-permissions.index', $role) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Permissões</a>
                            @endcan
                            @can('roles.edit')
                                <a href="{{ route('roles.edit', $role) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                            @endcan
                            @can('roles.update-order')
                                <form method="POST" action="{{ route('roles.update-order') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">▲</button>
                                </form>
                                <form method="POST" action="{{ route('roles.update-order') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 cursor-pointer">▼</button>
                                </form>
                            @endcan
                            @can('roles.destroy')
                                <x-confirm-modal :action="route('roles.destroy', $role)" method="DELETE"
                                                 title="Remover este papel?"
                                                 message="Usuários com este papel perdem as permissões associadas.">
                                    <x-slot:trigger><button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline cursor-pointer">Remover</button></x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                        </div>
                    @else
                        <span class="text-xs text-gray-400">papel protegido</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
