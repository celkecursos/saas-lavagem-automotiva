<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Ver task-23, seção 1.

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Super Admin');

    return $user;
}

test('super admin cria, edita e remove um papel', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = superAdmin();

    $this->actingAs($admin)->post(route('roles.store'), ['name' => 'Suporte'])
        ->assertRedirect(route('roles.index'));

    $role = Role::where('name', 'Suporte')->sole();

    $this->actingAs($admin)->put(route('roles.update', $role), ['name' => 'Suporte N1']);
    expect($role->fresh()->name)->toBe('Suporte N1');

    $this->actingAs($admin)->delete(route('roles.destroy', $role));
    expect(Role::whereKey($role->id)->exists())->toBeFalse();
});

test('papel Super Admin nao pode ser editado nem removido', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = superAdmin();
    $superAdminRole = Role::where('name', 'Super Admin')->sole();

    $this->actingAs($admin)
        ->put(route('roles.update', $superAdminRole), ['name' => 'Hackeado'])
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $superAdminRole))
        ->assertForbidden();

    expect($superAdminRole->fresh()->name)->toBe('Super Admin');
});

test('reordenar papeis troca a ordem com o vizinho, mas nao mexe no Super Admin', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = superAdmin();
    $administrador = Role::where('name', 'Administrador')->sole();
    $administradorOriginalOrder = $administrador->order;
    $suporte = Role::create(['name' => 'Suporte', 'guard_name' => 'web', 'order' => $administradorOriginalOrder + 1]);
    $suporteOriginalOrder = $suporte->order;

    $this->actingAs($admin)->post(route('roles.update-order'), [
        'role_id' => $suporte->id,
        'direction' => 'up',
    ]);

    expect($suporte->fresh()->order)->toBe($administradorOriginalOrder)
        ->and($administrador->fresh()->order)->toBe($suporteOriginalOrder);
});

test('super admin cria, edita e remove uma permission', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = superAdmin();

    $this->actingAs($admin)->post(route('permissions.store'), ['name' => 'relatorios.exportar'])
        ->assertRedirect(route('permissions.index'));

    $permission = Permission::where('name', 'relatorios.exportar')->sole();

    $this->actingAs($admin)->put(route('permissions.update', $permission), ['name' => 'relatorios.exportar-csv']);
    expect($permission->fresh()->name)->toBe('relatorios.exportar-csv');

    $this->actingAs($admin)->delete(route('permissions.destroy', $permission));
    expect(Permission::whereKey($permission->id)->exists())->toBeFalse();
});

test('administrador (sem roles.*/permissions.*) toma 403 nessas telas', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
    $this->actingAs($user)->get(route('permissions.index'))->assertForbidden();
});
