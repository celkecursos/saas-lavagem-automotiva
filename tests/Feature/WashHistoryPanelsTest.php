<?php

use App\Models\CarWash;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-8, seções 3 e 4 — histórico dos dois lados.

test('assinante ve seu proprio historico de lavagens em /lavagem/escolher', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $carWash = CarWash::factory()->approved()->create(['name' => 'Lava Jato Histórico']);
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $this->actingAs($user)->get('/lavagem/escolher')
        ->assertOk()
        ->assertSee('Lava Jato Histórico')
        ->assertSee('Histórico de lavagens');
});

test('lava-rapido ve so as lavagens do proprio car_wash em /painel/lavagens', function () {
    $carWash = CarWash::factory()->approved()->create();
    $otherCarWash = CarWash::factory()->approved()->create();
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    $cycle = SubscriptionCycle::factory()->create();
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => SubscriptionCycle::factory()->create()->id,
        'car_wash_id' => $otherCarWash->id,
    ]);

    $response = $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get('/painel/lavagens');

    $response->assertOk();

    expect(WashRedemption::where('car_wash_id', $carWash->id)->count())->toBe(1)
        ->and(WashRedemption::where('car_wash_id', $otherCarWash->id)->count())->toBe(1);
});
