<?php

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

// Ver task-3, seção 5, e task-11, seção 4.

function auditAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('mudanca de status em car_washes gera registro em audits com old/new corretos', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = auditAdmin();
    $carWash = \App\Models\CarWash::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)->post(route('car-washes.approve', $carWash));

    $response = $this->actingAs($admin)->get(route('audits.index'));

    $response->assertOk()->assertSee('CarWash');
});

test('filtro por modelo mostra so os registros daquele model', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = auditAdmin();
    $plan = Plan::factory()->create(['price_cents' => 1000]);

    $this->actingAs($admin)->put(route('payment-plans.update', $plan), [
        'name' => $plan->name,
        'price_cents' => 2000,
        'wash_quota' => $plan->wash_quota,
        'quota_period' => $plan->quota_period,
    ]);

    $response = $this->actingAs($admin)->get(route('audits.index', ['model' => Plan::class]));

    $response->assertOk()->assertSee('Plan');
});

test('admin sem a permission de auditoria toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('audits.index'))->assertForbidden();
});
