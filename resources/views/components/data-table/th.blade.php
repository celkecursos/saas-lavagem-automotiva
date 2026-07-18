{{-- Cabeçalho de coluna do <x-data-table>; com `sortable` + `field`
     vira link que alterna sort/direction via query string (preservando
     os demais filtros da URL). --}}
@props(['field' => null, 'sortable' => false])

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400']) }}>
    @if ($sortable && $field)
        @php
            $currentSort = request('sort');
            $currentDirection = request('direction', 'asc');
            $nextDirection = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
        @endphp
        <a href="{{ request()->fullUrlWithQuery(['sort' => $field, 'direction' => $nextDirection]) }}"
           class="inline-flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
            {{ $slot }}
            @if ($currentSort === $field)
                <span aria-hidden="true">{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
            @endif
        </a>
    @else
        {{ $slot }}
    @endif
</th>
