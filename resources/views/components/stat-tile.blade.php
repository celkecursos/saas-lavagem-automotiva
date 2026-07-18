{{-- Número grande + label, usado nos KPIs dos dashboards (task-14,
     seções 4 e 5). --}}
@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-backgroundseconddark rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 p-4']) }}>
    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $value }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $label }}</p>
</div>
