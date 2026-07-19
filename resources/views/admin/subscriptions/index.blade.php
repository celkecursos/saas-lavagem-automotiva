@extends('layouts.admin')

@section('title', 'Assinantes — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Assinantes</h1>

        <form method="GET" action="{{ route('subscriptions.index') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                <option value="">Todos os status</option>
                @foreach (['incomplete', 'active', 'past_due', 'canceled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="plan_id" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                <option value="">Todos os planos</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" @selected((string) request('plan_id') === (string) $plan->id)>{{ $plan->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$subscriptions" empty-message="Nenhuma assinatura encontrada">
        <x-slot:head>
            <x-data-table.th>Assinante</x-data-table.th>
            <x-data-table.th>Plano</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Renovação</x-data-table.th>
        </x-slot:head>

        @foreach ($subscriptions as $subscription)
            <tr>
                <td class="px-4 py-3">
                    <a href="{{ route('subscriptions.show', $subscription) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $subscription->user->name }}
                    </a>
                </td>
                <td class="px-4 py-3">{{ $subscription->plan->name }}</td>
                <td class="px-4 py-3"><x-badge :status="$subscription->status" /></td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $subscription->current_period_end?->format('d/m/Y') ?? '—' }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
