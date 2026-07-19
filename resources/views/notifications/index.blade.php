@extends('layouts.'.$layout)

@section('title', 'Notificações')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notificações</h1>

        @if ($notifications->contains(fn ($notification) => $notification->read_at === null))
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn-secondary">Marcar todas como lidas</button>
            </form>
        @endif
    </div>

    <x-data-table :rows="$notifications" empty-message="Nenhuma notificação ainda">
        <x-slot:head>
            <x-data-table.th></x-data-table.th>
            <x-data-table.th>Notificação</x-data-table.th>
            <x-data-table.th>Quando</x-data-table.th>
        </x-slot:head>

        @foreach ($notifications as $notification)
            <tr class="{{ $notification->read_at === null ? 'bg-blue-50/50 dark:bg-blue-950/20' : '' }}">
                <td class="px-4 py-3 w-4">
                    @if ($notification->read_at === null)
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-600" aria-label="Não lida"></span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <a href="{{ $notification->data['url'] ?? route('notifications.index') }}"
                       @if ($notification->read_at === null)
                           onclick="event.preventDefault(); fetch('{{ route('notifications.mark-read', $notification->id) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).finally(() => window.location.href = this.href);"
                       @endif
                       class="block">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $notification->data['title'] ?? '' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $notification->data['body'] ?? '' }}</p>
                    </a>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    {{ $notification->created_at->diffForHumans() }}
                </td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
