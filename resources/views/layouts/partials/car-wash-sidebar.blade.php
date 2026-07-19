{{-- Sidebar do lava-rápido — a regra de visibilidade mora em
     App\Support\CarWashPanelMenu (task-14, seção 3); a view só itera o
     resultado. Produtos ativos e papel vêm do car_wash atual da sessão
     (middleware SetCurrentCarWash, task-5 seção 7). --}}
@auth
    @php
        $currentCarWashId = session('current_car_wash_id');

        $activeProducts = $currentCarWashId
            ? \Illuminate\Support\Facades\DB::table('car_wash_product_subscriptions')
                ->where('car_wash_id', $currentCarWashId)
                ->where('status', 'active')
                ->pluck('product')
                ->all()
            : [];

        $roleInCarWash = $currentCarWashId
            ? (\Illuminate\Support\Facades\DB::table('car_wash_users')
                ->where('car_wash_id', $currentCarWashId)
                ->where('user_id', auth()->id())
                ->value('role') ?? 'employee')
            : 'employee';
    @endphp

    @foreach (\App\Support\CarWashPanelMenu::renderableItemsFor($activeProducts, $roleInCarWash) as $item)
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
        </a>
    @endforeach
@endauth
