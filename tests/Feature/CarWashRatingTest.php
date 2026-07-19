<?php

use App\Models\CarWash;
use App\Models\CarWashRating;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-8, seção 2, passo 7, e task-13, seção 2.4.

function completedRedemptionFor(User $user): array
{
    $carWash = CarWash::factory()->approved()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    return [$carWash, $redemption];
}

test('avaliar lavagem confirmada cria rating e recalcula satisfaction_score', function () {
    $user = User::factory()->create();
    [$carWash, $redemption] = completedRedemptionFor($user);

    $this->actingAs($user)
        ->post(route('wash.rate', $redemption), ['score' => 80])
        ->assertRedirect(route('wash.choose'));

    $rating = CarWashRating::sole();

    expect($rating->score)->toBe(80)
        ->and($rating->wash_redemption_id)->toBe($redemption->id)
        ->and((float) $carWash->fresh()->satisfaction_score)->toBe(80.0);
});

test('satisfaction_score e a media de todas as avaliacoes', function () {
    $carWash = CarWash::factory()->approved()->create();
    $plan = Plan::factory()->create();

    foreach ([100, 60] as $score) {
        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
        $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
        $redemption = WashRedemption::factory()->completed()->create([
            'subscription_cycle_id' => $cycle->id,
            'car_wash_id' => $carWash->id,
        ]);

        $this->actingAs($user)->post(route('wash.rate', $redemption), ['score' => $score]);
    }

    expect((float) $carWash->fresh()->satisfaction_score)->toBe(80.0);
});

test('avaliar a mesma lavagem duas vezes edita, nao duplica', function () {
    $user = User::factory()->create();
    [, $redemption] = completedRedemptionFor($user);

    $this->actingAs($user)->post(route('wash.rate', $redemption), ['score' => 50]);
    $this->actingAs($user)->post(route('wash.rate', $redemption), ['score' => 90, 'comment' => 'Editado']);

    expect(CarWashRating::count())->toBe(1)
        ->and(CarWashRating::sole()->score)->toBe(90)
        ->and(CarWashRating::sole()->comment)->toBe('Editado');
});

test('nao pode avaliar lavagem ainda nao confirmada', function () {
    $user = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'status' => 'requested',
    ]);

    $this->actingAs($user)
        ->post(route('wash.rate', $redemption), ['score' => 80])
        ->assertSessionHas('error');

    expect(CarWashRating::count())->toBe(0);
});

test('outro usuario nao pode avaliar lavagem que nao e dele', function () {
    $owner = User::factory()->create();
    [, $redemption] = completedRedemptionFor($owner);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)
        ->post(route('wash.rate', $redemption), ['score' => 80])
        ->assertForbidden();
});
