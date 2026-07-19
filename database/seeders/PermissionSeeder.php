<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Lista COMPLETA de permissions do projeto, consolidada na task-3
     * (seção 6) a partir do que cada task usa — convenção "recurso.acao".
     * Semeada aqui (antes das telas existirem) porque rotas das tasks
     * 4/5/9 já usam middleware permission:* muito antes da task-11/23.
     *
     * @var array<int, string>
     */
    private array $permissions = [
        'car-washes.index',
        'car-washes.approve',
        'car-washes.reject',
        'car-washes.suspend',
        'car-wash-product-subscriptions.index',
        'car-wash-product-subscriptions.approve',
        'car-wash-product-subscriptions.reject',
        'payout-plans.index',
        'payout-plans.create',
        'payout-plans.edit',
        'payment-gateways.index',
        'payment-gateways.create',
        'payment-gateways.edit',
        'payment-gateways.activate',
        'payment-plans.index',
        'payment-plans.create',
        'payment-plans.edit',
        'orders.index',
        'orders.show',
        'orders.refund',
        'order-refund-requests.index',
        'order-refund-requests.mark-processed',
        'refund-settings.edit',
        'users.index',
        'users.show',
        'users.suspend',
        'users.reactivate',
        'payouts.index',
        'payouts.mark-paid',
        'payouts.mark-failed',
        'cancellation-requests.index',
        'cancellation-requests.approve',
        'cancellation-requests.reject',
        'parking-billing-settings.edit',
        'parking-billing-charges.index',
        'audits.index',
        'roles.index',
        'roles.create',
        'roles.edit',
        'roles.destroy',
        'roles.update-order',
        'permissions.index',
        'permissions.create',
        'permissions.edit',
        'permissions.destroy',
        'role-permissions.index',
        'role-permissions.update',
        'achievements.index',
        'achievements.create',
        'achievements.edit',
        'loyalty-redemptions.index',
        'loyalty-redemptions.create',
        'loyalty-redemptions.edit',
    ];

    public function run(): void
    {
        // updateOrCreate por name+guard_name: rodar o seeder 2x não
        // duplica nada (ver task-13, seção 2.6.2).
        foreach ($this->permissions as $name) {
            Permission::updateOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
