@extends('layouts.admin')

@section('title', 'Usuários — Admin')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Usuários</h1>

    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-2 mb-4">
        <input type="text" name="search" value="{{ $search }}" placeholder="Nome, e-mail, CPF ou telefone"
               class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200 flex-1 min-w-[220px]">
        <select name="role" onchange="this.form.submit()"
                class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
            <option value="">Todos os papéis</option>
            <option value="subscriber" @selected($role === 'subscriber')>Assinante ativo</option>
            <option value="car-wash" @selected($role === 'car-wash')>Dono/funcionário de lava-rápido</option>
            <option value="admin" @selected($role === 'admin')>Admin</option>
            <option value="suspended" @selected($role === 'suspended')>Suspenso</option>
        </select>
        <button type="submit" class="btn-primary">Buscar</button>
    </form>

    <x-data-table :rows="$users" empty-message="Nenhum usuário encontrado">
        <x-slot:head>
            <x-data-table.th>Nome</x-data-table.th>
            <x-data-table.th>E-mail</x-data-table.th>
            <x-data-table.th>Papéis</x-data-table.th>
            <x-data-table.th>Cadastro</x-data-table.th>
        </x-slot:head>

        @foreach ($users as $user)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('users.show', $user) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $user->name }}
                    </a>
                </td>
                <td class="px-4 py-3">{{ $user->email }}</td>
                <td class="px-4 py-3 flex flex-wrap gap-1">
                    @if ($user->role === 'admin')
                        <x-badge status="admin" variant="info">admin</x-badge>
                    @endif
                    @if ($user->subscriptions_count > 0)
                        <x-badge status="active" variant="success">assinante</x-badge>
                    @endif
                    @if ($user->carWashes->isNotEmpty())
                        <x-badge status="car-wash" variant="secondary">lava-rápido</x-badge>
                    @endif
                    @if ($user->suspended_at)
                        <x-badge status="suspended" variant="danger">suspenso</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
