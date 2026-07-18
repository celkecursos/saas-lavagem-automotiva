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
        <a href="{{ route($item['route']) }}"
           class="flex items-center gap-2 rounded px-3 py-2 text-sm {{ request()->routeIs($item['route']) ? 'bg-background text-gray-200' : 'text-gray-400 hover:text-gray-200 hover:bg-background/60' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
@endauth
