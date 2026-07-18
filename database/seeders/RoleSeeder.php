<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Papéis da plataforma (task-3, seção 6 / task-23):
     *
     * - Super Admin: order=1, NENHUMA permission explícita — o
     *   Gate::before do AppServiceProvider já libera tudo pra ele.
     * - Administrador: todas as permissions EXCETO roles.* /
     *   permissions.* / role-permissions.* (gerenciar papéis e
     *   permissões é exclusivo do Super Admin por padrão).
     */
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['order' => 1],
        );

        $administrador = Role::updateOrCreate(
            ['name' => 'Administrador', 'guard_name' => 'web'],
            ['order' => 2],
        );

        $administrador->syncPermissions(
            Permission::where('guard_name', 'web')
                ->whereNot(function ($query) {
                    $query->where('name', 'like', 'roles.%')
                        ->orWhere('name', 'like', 'permissions.%')
                        ->orWhere('name', 'like', 'role-permissions.%');
                })
                ->get(),
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
