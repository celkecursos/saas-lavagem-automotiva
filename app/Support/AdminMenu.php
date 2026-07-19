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
 */
class AdminMenu
{
    /**
     * @return array<int, array{label: string, route: string, permission: ?string, badge: ?Closure}>
     */
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'permission' => null,
                'badge' => null,
            ],
            [
                'label' => 'Lava-rápidos',
                'route' => 'car-washes.index',
                'permission' => 'car-washes.index',
                'badge' => null,
            ],
            [
                'label' => 'Ativação do clube de lavagem',
                'route' => 'car-wash-product-subscriptions.index',
                'permission' => 'car-wash-product-subscriptions.index',
                'badge' => fn (): int => DB::table('car_wash_product_subscriptions')
                    ->where('product', 'clube_lavagem')
                    ->where('status', 'pending')
                    ->count(),
            ],
            [
                'label' => 'Planos',
                'route' => 'payment-plans.index',
                'permission' => 'payment-plans.index',
                'badge' => null,
            ],
            [
                'label' => 'Planos de repasse',
                'route' => 'payout-plans.index',
                'permission' => 'payout-plans.index',
                'badge' => null,
            ],
            [
                'label' => 'Assinantes',
                'route' => 'subscriptions.index',
                'permission' => 'users.index',
                'badge' => null,
            ],
            [
                'label' => 'Pedidos',
                'route' => 'orders.index',
                'permission' => 'orders.index',
                'badge' => null,
            ],
            [
                'label' => 'Repasses',
                'route' => 'payouts.index',
                'permission' => 'payouts.index',
                'badge' => fn (): int => DB::table('payouts')
                    ->where('status', 'pending')
                    ->count(),
            ],
            [
                'label' => 'Solicitações de cancelamento',
                'route' => 'cancellation-requests.index',
                'permission' => 'cancellation-requests.index',
                'badge' => fn (): int => DB::table('cancellation_requests')
                    ->where('status', 'pending')
                    ->count(),
            ],
            [
                'label' => 'Gateways de pagamento',
                'route' => 'payment-gateways.index',
                'permission' => 'payment-gateways.index',
                'badge' => null,
            ],
            [
                'label' => 'Configurações do estacionamento',
                'route' => 'parking-billing-settings.edit',
                'permission' => 'parking-billing-settings.edit',
                'badge' => null,
            ],
            [
                'label' => 'Cobranças do estacionamento',
                'route' => 'parking-billing-charges.index',
                'permission' => 'parking-billing-charges.index',
                'badge' => fn (): int => DB::table('parking_billing_charges')
                    ->where('flagged_for_review', true)
                    ->count(),
            ],
            [
                'label' => 'Reembolsos pendentes',
                'route' => 'order-refund-requests.index',
                'permission' => 'order-refund-requests.index',
                'badge' => fn (): int => DB::table('order_refund_requests')
                    ->where('status', 'failed_manual')
                    ->count(),
            ],
            [
                'label' => 'Configurações de reembolso',
                'route' => 'refund-settings.edit',
                'permission' => 'refund-settings.edit',
                'badge' => null,
            ],
            [
                'label' => 'Auditoria',
                'route' => 'audits.index',
                'permission' => 'audits.index',
                'badge' => null,
            ],
            [
                'label' => 'Papéis',
                'route' => 'roles.index',
                'permission' => 'roles.index',
                'badge' => null,
            ],
            [
                'label' => 'Permissões',
                'route' => 'permissions.index',
                'permission' => 'permissions.index',
                'badge' => null,
            ],
        ];
    }
}
