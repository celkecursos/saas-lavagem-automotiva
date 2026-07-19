@extends('layouts.admin')

@section('title', 'Solicitações de cancelamento — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Solicitações de cancelamento</h1>

        <form method="GET" action="{{ route('cancellation-requests.index') }}">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-700 dark:text-gray-200">
                @foreach (['pending' => 'Pendentes', 'approved' => 'Aprovadas', 'rejected' => 'Rejeitadas', 'all' => 'Todas'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <x-data-table :rows="$cancellationRequests" empty-message="Nenhuma solicitação nesta situação">
        <x-slot:head>
            <x-data-table.th>Solicitado por</x-data-table.th>
            <x-data-table.th>Motivo</x-data-table.th>
            <x-data-table.th>Status</x-data-table.th>
            <x-data-table.th>Ações</x-data-table.th>
        </x-slot:head>

        @foreach ($cancellationRequests as $cancellationRequest)
            <tr>
                <td class="px-4 py-3">{{ $cancellationRequest->requestedBy->name }}</td>
                <td class="px-4 py-3">{{ $cancellationRequest->reason }}</td>
                <td class="px-4 py-3"><x-badge :status="$cancellationRequest->status" /></td>
                <td class="px-4 py-3">
                    @if ($cancellationRequest->status === 'pending')
                        <div class="flex items-center gap-3">
                            @can('cancellation-requests.approve')
                                <x-confirm-modal :action="route('cancellation-requests.approve', $cancellationRequest)"
                                                 title="Aprovar este cancelamento?"
                                                 message="A lavagem vira 'canceled'. Se o ciclo ainda for o atual, a cota é devolvida. Se já entrou num repasse pago, o estorno fica pendente pra revisão manual.">
                                    <x-slot:trigger><button type="button" class="text-sm text-green-600 dark:text-green-400 hover:underline cursor-pointer">Aprovar</button></x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                            @can('cancellation-requests.reject')
                                <x-confirm-modal :action="route('cancellation-requests.reject', $cancellationRequest)"
                                                 title="Rejeitar este cancelamento?"
                                                 message="A lavagem permanece 'completed' sem nenhuma mudança.">
                                    <x-slot:trigger><button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline cursor-pointer">Rejeitar</button></x-slot:trigger>
                                </x-confirm-modal>
                            @endcan
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
