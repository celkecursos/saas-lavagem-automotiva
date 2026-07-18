{{-- Itens da sidebar do admin — preenchida via App\Support\AdminMenu
     (task-14, seção 2). --}}
@if (Route::has('admin.dashboard'))
    <a href="{{ route('admin.dashboard') }}"
       class="flex items-center gap-2 rounded px-3 py-2 text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-background text-gray-200' : 'text-gray-400 hover:text-gray-200 hover:bg-background/60' }}">
        Dashboard
    </a>
@endif
