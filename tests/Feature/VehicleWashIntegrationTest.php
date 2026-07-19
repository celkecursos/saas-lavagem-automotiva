<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WashRedemption;

// Ver task-15, seção 3, e task-13, seção 2.4 (integração com veículos).

function washReadyCarWash(): CarWash
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
    ]);

    return $carWash;
}

function washReadySubscriber(): array
{
    $plan = Plan::factory()->create();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id, 'quota_total' => 4]);

    return [$user, $cycle];
}

test('assinante sem nenhum veiculo ativo e redirecionado antes de gerar codigo', function () {
    [$user] = washReadySubscriber();
    $carWash = washReadyCarWash();

    $this->actingAs($user)->get('/lavagem/escolher')
        ->assertRedirect(route('vehicles.create'));

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertRedirect(route('vehicles.create'));

    expect(WashRedemption::count())->toBe(0);
});

test('assinante com 1 veiculo so: gera o codigo sem precisar escolher', function () {
    [$user] = washReadySubscriber();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
    $carWash = washReadyCarWash();

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertRedirect(route('wash.choose'));

    expect(WashRedemption::sole()->vehicle_id)->toBe($vehicle->id);
});

test('assinante com 2+ veiculos precisa escolher, e o vehicle_id gravado e o correto', function () {
    [$user] = washReadySubscriber();
    Vehicle::factory()->create(['user_id' => $user->id]);
    $chosen = Vehicle::factory()->create(['user_id' => $user->id]);
    $carWash = washReadyCarWash();

    $this->actingAs($user)
        ->post(route('wash.request', $carWash), ['vehicle_id' => $chosen->id])
        ->assertRedirect(route('wash.choose'));

    expect(WashRedemption::sole()->vehicle_id)->toBe($chosen->id);
});

test('assinante com 2+ veiculos sem escolher nenhum e bloqueado', function () {
    [$user] = washReadySubscriber();
    Vehicle::factory()->create(['user_id' => $user->id]);
    Vehicle::factory()->create(['user_id' => $user->id]);
    $carWash = washReadyCarWash();

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertSessionHas('error');

    expect(WashRedemption::count())->toBe(0);
});

test('veiculo arquivado (active=false) nao conta pra escolha nem pro bloqueio', function () {
    [$user] = washReadySubscriber();
    Vehicle::factory()->create(['user_id' => $user->id, 'active' => false]);
    $carWash = washReadyCarWash();

    // Só tem veículo inativo -> conta como "sem veículo".
    $this->actingAs($user)->get('/lavagem/escolher')
        ->assertRedirect(route('vehicles.create'));
});

test('remover (soft delete) um veiculo nao quebra o historico de wash_redemptions antigos', function () {
    [$user, $cycle] = washReadySubscriber();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'vehicle_id' => $vehicle->id,
    ]);

    $this->actingAs($user)->delete(route('vehicles.destroy', $vehicle));

    expect($redemption->fresh()->vehicle_id)->toBe($vehicle->id)
        ->and($redemption->fresh()->vehicle->plate)->toBe($vehicle->plate);
});
