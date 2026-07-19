{{-- Sidebar do admin dirigida por App\Support\AdminMenu (task-14,
     seção 2): Route::has() primeiro (rota pode ainda não existir nas
     tasks iniciais), permission depois. O ícone vem do item como path
     do Heroicons; o <svg> é montado aqui. --}}
@foreach (\App\Support\AdminMenu::items() as $item)
    @if (Route::has($item['route']) && ($item['permission'] === null || auth()->user()?->can($item['permission'])))
        @php($badgeCount = $item['badge'] ? ($item['badge'])() : 0)
        @php($isActive = request()->routeIs($item['route']))
        <a href="{{ route($item['route']) }}"
           @class([
               'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition',
               'bg-background text-gray-100' => $isActive,
               'text-gray-400 hover:bg-background/60 hover:text-gray-100' => ! $isActive,
           ])>
            <svg @class([
                    'h-5 w-5 shrink-0 transition',
                    'text-blue-400' => $isActive,
                    'text-gray-500 group-hover:text-gray-300' => ! $isActive,
                 ])
                 fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
            </svg>
            <span class="flex-1 leading-tight">{{ $item['label'] }}</span>
            @if ($badgeCount > 0)
                <span class="badge-warning shrink-0">{{ $badgeCount }}</span>
            @endif
        </a>
    @endif
@endforeach
