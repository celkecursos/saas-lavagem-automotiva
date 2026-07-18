{{-- Itens da sidebar do lava-rápido — regra de visibilidade em
     App\Support\CarWashPanelMenu (task-14, seção 3). --}}
@if (Route::has('panel.dashboard'))
    <a href="{{ route('panel.dashboard') }}"
       class="flex items-center gap-2 rounded px-3 py-2 text-sm {{ request()->routeIs('panel.dashboard') ? 'bg-background text-gray-200' : 'text-gray-400 hover:text-gray-200 hover:bg-background/60' }}">
        Dashboard
    </a>
@endif
