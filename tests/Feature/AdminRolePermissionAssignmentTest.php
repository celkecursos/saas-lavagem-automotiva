<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Ver task-23, seção 2, e task-13, seção 2.6.2.

function assignmentSuperAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Super Admin');

    return $user;
}

test('acessar role-permissions.index pro papel Super Admin e bloqueado', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = assignmentSuperAdmin();
    $superAdminRole = Role::where('name', 'Super Admin')->sole();

    $this->actingAs($admin)
        ->get(route('role-permissions.index', $superAdminRole))
        ->assertForbidden();
});

test('toggle de uma permission pro papel Administrador reflete na hora', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = assignmentSuperAdmin();
    $administrador = Role::where('name', 'Administrador')->sole();

    // 'roles.index' não é dada ao Administrador por padrão (task-3, §6).
    $rolesIndex = Permission::where('name', 'roles.index')->sole();

    $target = User::factory()->create();
    $target->forceFill(['role' => 'admin'])->save();
    $target->assignRole('Administrador');

    $this->actingAs($target)->get(route('roles.index'))->assertForbidden();

    // Super Admin libera a permission pro papel Administrador.
    $this->actingAs($admin)->post(route('role-permissions.update', [$administrador, $rolesIndex]));

    $this->actingAs($target)->get(route('roles.index'))->assertOk();

    // Revoga de novo.
    $this->actingAs($admin)->post(route('role-permissions.update', [$administrador, $rolesIndex]));

    $this->actingAs($target)->get(route('roles.index'))->assertForbidden();
});

test('index mostra indicador Liberado/Bloqueado corretamente por permission', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = assignmentSuperAdmin();
    $administrador = Role::where('name', 'Administrador')->sole();

    $response = $this->actingAs($admin)->get(route('role-permissions.index', $administrador));

    $response->assertOk()
        // Administrador já tem payouts.mark-paid por padrão, mas não
        // tem roles.index (exclusiva do Super Admin) — ambos os
        // indicadores aparecem na tela.
        ->assertSee('Liberado')
        ->assertSee('Bloqueado')
        ->assertSee('payouts.mark-paid')
        ->assertSee('roles.index');
});
