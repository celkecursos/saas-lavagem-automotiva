<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// Ver task-13, seção 2.6.2 — a fundação (pacote + Gate::before + seed)
// nasce na task-3; as telas de gerenciar isso só chegam na task-23.

test('rodar o seeder duas vezes nao duplica permissions nem roles', function () {
    $this->seed(DatabaseSeeder::class);
    $permissions = Permission::count();
    $roles = Role::count();

    $this->seed(DatabaseSeeder::class);

    expect(Permission::count())->toBe($permissions)
        ->and(Role::count())->toBe($roles);
});

test('super admin passa em qualquer ability via Gate::before, sem permission explicita', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    expect($user->can('payouts.mark-paid'))->toBeTrue()
        ->and($user->can('ability-que-nem-existe'))->toBeTrue()
        ->and(Role::findByName('Super Admin')->permissions()->count())->toBe(0);
});

test('administrador tem as permissions operacionais mas nao as de gerenciar papeis', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Administrador');

    expect($user->can('payouts.mark-paid'))->toBeTrue()
        ->and($user->can('roles.index'))->toBeFalse()
        ->and($user->can('permissions.index'))->toBeFalse()
        ->and($user->can('role-permissions.update'))->toBeFalse();
});
