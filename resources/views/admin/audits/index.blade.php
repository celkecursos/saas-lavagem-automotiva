@extends('layouts.admin')

@section('title', 'Auditoria — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Auditoria</h1>

        <form method="GET" action="{{ route('audits.index') }}" class="flex items-center gap-2">
            <select name="model" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                <option value="">Todos os modelos</option>
                @foreach ($models as $model)
                    <option value="{{ $model }}" @selected(request('model') === $model)>{{ class_basename($model) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()"
                   class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
            <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()"
                   class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
        </form>
    </div>

    <x-data-table :rows="$audits" empty-message="Nenhum registro de auditoria encontrado">
        <x-slot:head>
            <x-data-table.th>Quando</x-data-table.th>
            <x-data-table.th>Quem</x-data-table.th>
            <x-data-table.th>Modelo</x-data-table.th>
            <x-data-table.th>Evento</x-data-table.th>
            <x-data-table.th>Alterações</x-data-table.th>
        </x-slot:head>

        @foreach ($audits as $audit)
            <tr>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">{{ $audit->user?->name ?? 'Sistema' }}</td>
                <td class="px-4 py-3">{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</td>
                <td class="px-4 py-3"><x-badge :status="$audit->event" variant="secondary" /></td>
                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                    @foreach ($audit->new_values ?? [] as $field => $newValue)
                        <div>
                            <strong>{{ $field }}:</strong>
                            {{ $audit->old_values[$field] ?? '—' }} → {{ $newValue }}
                        </div>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
