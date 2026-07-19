@extends('layouts.admin')

@section('title', 'Permissões — '.$role->name)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Permissões — {{ $role->name }}</h1>
        <a href="{{ route('roles.index') }}" class="btn-secondary">Voltar</a>
    </div>

    <x-data-table :rows="$permissions" empty-message="Nenhuma permission cadastrada">
        <x-slot:head>
            <x-data-table.th>Permission</x-data-table.th>
            <x-data-table.th>Situação</x-data-table.th>
            <x-data-table.th></x-data-table.th>
        </x-slot:head>

        @foreach ($permissions as $permission)
            @php($granted = in_array($permission->id, $grantedIds))
            <tr>
                <td class="px-4 py-3 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $permission->name }}</td>
                <td class="px-4 py-3">
                    @if ($granted)
                        <x-badge status="active" variant="success">Liberado</x-badge>
                    @else
                        <x-badge status="inactive" variant="secondary">Bloqueado</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('role-permissions.update', [$role, $permission]) }}">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                            {{ $granted ? 'Bloquear' : 'Liberar' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
