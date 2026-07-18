<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-13, seção 2.4, e task-8, seção 2.

function activeCarWash(): CarWash
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
    ]);

    return $carWash;
}

function activeSubscriberWithCycle(int $quotaTotal = 4, int $quotaUsed = 0, ?int $maxPerDay = null): array
{
    $plan = Plan::factory()->create(['max_redemptions_per_day_per_car_wash' => $maxPerDay]);
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_total' => $quotaTotal,
        'quota_used' => $quotaUsed,
    ]);

    return [$user, $subscription, $cycle];
}

test('escolher lava-rapido lista so aprovados com clube de lavagem ativo', function () {
    $visible = activeCarWash();
    $notApproved = CarWash::factory()->create(['status' => 'pending', 'name' => 'Nao Aprovado']);
    $noClub = CarWash::factory()->approved()->create(['name' => 'Sem Clube']);

    [$user] = activeSubscriberWithCycle();

    $response = $this->actingAs($user)->get('/lavagem/escolher');

    $response->assertOk()
        ->assertSee($visible->name)
        ->assertDontSee('Nao Aprovado')
        ->assertDontSee('Sem Clube');
});

test('gerar codigo nao debita quota_used', function () {
    $carWash = activeCarWash();
    [$user, , $cycle] = activeSubscriberWithCycle(4, 0);

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertRedirect(route('wash.choose'));

    $redemption = WashRedemption::sole();

    expect($redemption->status)->toBe('requested')
        ->and($redemption->confirmation_code)->toHaveLength(6)
        ->and($redemption->code_expires_at)->not->toBeNull()
        ->and($cycle->fresh()->quota_used)->toBe(0);
});

test('sem cota disponivel, pedido de novo codigo e bloqueado', function () {
    $carWash = activeCarWash();
    [$user] = activeSubscriberWithCycle(4, 4);

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(WashRedemption::count())->toBe(0);
});

test('ja existe codigo requested valido: bloqueia gerar outro', function () {
    $carWash = activeCarWash();
    [$user, , $cycle] = activeSubscriberWithCycle();
    WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertSessionHas('error');

    expect(WashRedemption::count())->toBe(1);
});

test('max_redemptions_per_day_per_car_wash e respeitado quando definido', function () {
    $carWash = activeCarWash();
    [$user, , $cycle] = activeSubscriberWithCycle(quotaTotal: 10, maxPerDay: 1);
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'redeemed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('wash.request', $carWash))
        ->assertSessionHas('error');

    expect(WashRedemption::where('status', 'requested')->count())->toBe(0);
});

test('assinante cancela o proprio codigo requested', function () {
    $carWash = activeCarWash();
    [$user, , $cycle] = activeSubscriberWithCycle();
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $this->actingAs($user)
        ->post(route('wash.cancel', $redemption))
        ->assertRedirect(route('wash.choose'));

    expect($redemption->fresh()->status)->toBe('canceled');
});

test('outro usuario nao pode cancelar codigo que nao e dele', function () {
    $carWash = activeCarWash();
    [, , $cycle] = activeSubscriberWithCycle();
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('wash.cancel', $redemption))
        ->assertForbidden();
});
