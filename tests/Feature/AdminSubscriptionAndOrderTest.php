<?php

use App\Models\CarWash;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use Database\Seeders\DatabaseSeeder;

// Ver task-11, seção 4.

function subscriptionAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('lista de assinaturas filtra por status e plano', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = subscriptionAdmin();
    $planA = Plan::factory()->create(['name' => 'Plano A']);
    $planB = Plan::factory()->create(['name' => 'Plano B']);
    Subscription::factory()->for($planA, 'plan')->active()->create(['user_id' => User::factory()->create(['name' => 'Ana'])->id]);
    Subscription::factory()->for($planB, 'plan')->create(['status' => 'canceled', 'user_id' => User::factory()->create(['name' => 'Bruno'])->id]);

    $response = $this->actingAs($admin)->get(route('subscriptions.index', ['status' => 'active']));

    $response->assertOk()->assertSee('Ana')->assertDontSee('Bruno');
});

test('detalhe da assinatura mostra ciclos, pedidos e lavagens', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = subscriptionAdmin();
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    Order::factory()->create([
        'user_id' => $user->id,
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'amount_cents' => 4990,
    ]);
    $carWash = CarWash::factory()->approved()->create(['name' => 'Lava Detalhe']);
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $response = $this->actingAs($admin)->get(route('subscriptions.show', $subscription));

    $response->assertOk()
        ->assertSee($user->name)
        ->assertSee('Lava Detalhe')
        ->assertSee('R$ 49,90');
});

test('admin sem permission de assinantes toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('subscriptions.index'))->assertForbidden();
});

test('lista de pedidos filtra por status', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = subscriptionAdmin();
    Order::factory()->create(['status' => 'paid', 'user_id' => User::factory()->create(['name' => 'Pago Fulano'])->id]);
    Order::factory()->create(['status' => 'failed', 'user_id' => User::factory()->create(['name' => 'Falhou Ciclano'])->id]);

    $response = $this->actingAs($admin)->get(route('orders.index', ['status' => 'paid']));

    $response->assertOk()->assertSee('Pago Fulano')->assertDontSee('Falhou Ciclano');
});

test('detalhe do pedido mostra dados do gateway e referencia', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = subscriptionAdmin();
    $order = Order::factory()->create(['external_reference' => 'CHAR_TESTE_123']);

    $this->actingAs($admin)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('CHAR_TESTE_123');
});

test('admin sem permission de pedidos toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    $this->actingAs($user)->get(route('orders.index'))->assertForbidden();
});
