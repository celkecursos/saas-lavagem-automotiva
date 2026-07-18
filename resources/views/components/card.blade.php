{{-- Container padrão com título opcional (task-14, seção 6). --}}
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-backgroundseconddark rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-4']) }}>
    @if ($title)
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
