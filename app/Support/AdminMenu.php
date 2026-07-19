<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Itens da sidebar do admin (task-14, seção 2) como array de config —
 * não Blade solto. A view só renderiza um item se Route::has($route)
 * E o usuário tiver a permission (nessa ordem: rota primeiro, mais
 * barata). Itens de rotas ainda não registradas (tasks 4/5/9/10/11)
 * simplesmente não aparecem até a task correspondente criá-las.
 *
 * Contadores de badge são queries diretas (count) — volume esperado
 * na v1 não justifica cache/job.
 *
 * 'icon' guarda só o atributo "d" de um path do Heroicons outline
 * (24x24, stroke). A view monta o <svg> em volta, então o ícone não
 * carrega markup nem classe de tamanho — quem decide isso é o layout.
 * Ícones com mais de um traço concatenam os subpaths no mesmo "d".
 */
class AdminMenu
{
    /**
     * @return array<int, array{label: string, route: string, permission: ?string, badge: ?Closure, icon: string}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'permission' => null,
                'badge' => null,
                'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
            ],
            [
                'label' => 'Lava-rápidos',
                'route' => 'car-washes.index',
                'permission' => 'car-washes.index',
                'badge' => null,
                'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
            ],
            [
                'label' => 'Ativação do clube de lavagem',
                'route' => 'car-wash-product-subscriptions.index',
                'permission' => 'car-wash-product-subscriptions.index',
                'badge' => fn (): int => DB::table('car_wash_product_subscriptions')
                    ->where('product', 'clube_lavagem')
                    ->where('status', 'pending')
                    ->count(),
                'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Planos',
                'route' => 'payment-plans.index',
                'permission' => 'payment-plans.index',
                'badge' => null,
                'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
            ],
            [
                'label' => 'Planos de repasse',
                'route' => 'payout-plans.index',
                'permission' => 'payout-plans.index',
                'badge' => null,
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Usuários',
                'route' => 'users.index',
                'permission' => 'users.index',
                'badge' => null,
                'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
            ],
            [
                'label' => 'Conquistas',
                'route' => 'achievements.index',
                'permission' => 'achievements.index',
                'badge' => null,
                'icon' => 'M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0',
            ],
            [
                'label' => 'Recompensas de fidelidade',
                'route' => 'loyalty-redemptions.index',
                'permission' => 'loyalty-redemptions.index',
                'badge' => null,
                'icon' => 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
            ],
            [
                'label' => 'Assinantes',
                'route' => 'subscriptions.index',
                'permission' => 'users.index',
                'badge' => null,
                'icon' => 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            [
                'label' => 'Pedidos',
                'route' => 'orders.index',
                'permission' => 'orders.index',
                'badge' => null,
                'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z',
            ],
            [
                'label' => 'Repasses',
                'route' => 'payouts.index',
                'permission' => 'payouts.index',
                'badge' => fn (): int => DB::table('payouts')
                    ->where('status', 'pending')
                    ->count(),
                'icon' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
            ],
            [
                'label' => 'Solicitações de cancelamento',
                'route' => 'cancellation-requests.index',
                'permission' => 'cancellation-requests.index',
                'badge' => fn (): int => DB::table('cancellation_requests')
                    ->where('status', 'pending')
                    ->count(),
                'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Gateways de pagamento',
                'route' => 'payment-gateways.index',
                'permission' => 'payment-gateways.index',
                'badge' => null,
                'icon' => 'M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3',
            ],
            [
                'label' => 'Configurações do estacionamento',
                'route' => 'parking-billing-settings.edit',
                'permission' => 'parking-billing-settings.edit',
                'badge' => null,
                'icon' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75',
            ],
            [
                'label' => 'Cobranças do estacionamento',
                'route' => 'parking-billing-charges.index',
                'permission' => 'parking-billing-charges.index',
                'badge' => fn (): int => DB::table('parking_billing_charges')
                    ->where('flagged_for_review', true)
                    ->count(),
                'icon' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z',
            ],
            [
                'label' => 'Reembolsos pendentes',
                'route' => 'order-refund-requests.index',
                'permission' => 'order-refund-requests.index',
                'badge' => fn (): int => DB::table('order_refund_requests')
                    ->where('status', 'failed_manual')
                    ->count(),
                'icon' => 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3',
            ],
            [
                'label' => 'Configurações de reembolso',
                'route' => 'refund-settings.edit',
                'permission' => 'refund-settings.edit',
                'badge' => null,
                'icon' => 'M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205L12 12m6.894 5.785l-1.149-.964M20.905 7.5l-1.41.513',
            ],
            [
                'label' => 'Auditoria',
                'route' => 'audits.index',
                'permission' => 'audits.index',
                'badge' => null,
                'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z',
            ],
            [
                'label' => 'Papéis',
                'route' => 'roles.index',
                'permission' => 'roles.index',
                'badge' => null,
                'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
            ],
            [
                'label' => 'Permissões',
                'route' => 'permissions.index',
                'permission' => 'permissions.index',
                'badge' => null,
                'icon' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
            ],
        ];
    }
}
