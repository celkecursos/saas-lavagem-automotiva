@extends('layouts.admin')

@section('title', 'Lava-rápidos — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Lava-rápidos</h1>

        {{-- Filtro por status (padrão: pending, verificados primeiro). --}}
        <form method="GET" action="{{ route('car-washes.index') }}">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                @foreach (['pending' => 'Pendentes', 'approved' => 'Aprovados', 'rejected' => 'Rejeitados', 'suspended' => 'Suspensos', 'all' => 'Todos'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$carWashes" empty-message="Nenhum lava-rápido nesta situação">
        <x-slot:head>
            <x-data-table.th>Estabelecimento</x-data-table.th>
            <x-data-table.th>Cidade</x-data-table.th>
            <x-data-table.th>E-mail verificado</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Cadastro</x-data-table.th>
        </x-slot:head>

        @foreach ($carWashes as $carWash)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('car-washes.show', $carWash) }}"
                       class="font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $carWash->name }}</a>
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $carWash->document }}</span>
                </td>
                <td class="px-4 py-3">{{ $carWash->city }}/{{ $carWash->state }}</td>
                <td class="px-4 py-3">
                    @if ($carWash->owner_email_verified_at)
                        <x-badge status="verified" variant="success">verificado</x-badge>
                    @else
                        <x-badge status="unverified" variant="secondary">não verificado</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3"><x-badge :status="$carWash->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $carWash->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
