{{-- Sidebar do admin dirigida por App\Support\AdminMenu (task-14,
     seção 2): Route::has() primeiro (rota pode ainda não existir nas
     tasks iniciais), permission depois. --}}
@foreach (\App\Support\AdminMenu::items() as $item)
    @if (Route::has($item['route']) && ($item['permission'] === null || auth()->user()?->can($item['permission'])))
        @php($badgeCount = $item['badge'] ? ($item['badge'])() : 0)
        <a href="{{ route($item['route']) }}"
           class="flex items-center justify-between gap-2 rounded px-3 py-2 text-sm {{ request()->routeIs($item['route']) ? 'bg-background text-gray-200' : 'text-gray-400 hover:text-gray-200 hover:bg-background/60' }}">
            <span>{{ $item['label'] }}</span>
            @if ($badgeCount > 0)
                <span class="badge-warning">{{ $badgeCount }}</span>
            @endif
        </a>
    @endif
@endforeach
