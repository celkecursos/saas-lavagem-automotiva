<?php

use App\Models\CarWash;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\SubscriptionCycle;
use App\Models\WashRedemption;

// Ver task-9, seções 1 e 2, e task-13, seção 2.5.

function completedRedemption(CarWash $carWash, int $basePriceCents): WashRedemption
{
    $cycle = SubscriptionCycle::factory()->create();

    return WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'base_price_cents_snapshot' => $basePriceCents,
    ]);
}

test('payouts:generate agrupa wash_redemptions completed sem payout_item_id por car_wash', function () {
    $carWash = CarWash::factory()->approved()->create(['satisfaction_score' => null]);
    completedRedemption($carWash, 2000);
    completedRedemption($carWash, 3000);

    $this->artisan('payouts:generate')->assertSuccessful();

    $payout = Payout::sole();

    expect($payout->car_wash_id)->toBe($carWash->id)
        ->and(PayoutItem::where('payout_id', $payout->id)->count())->toBe(2);
});

test('rodar o comando 2x seguidas sem novas lavagens nao gera payout duplicado nem vazio', function () {
    $carWash = CarWash::factory()->approved()->create();
    completedRedemption($carWash, 2000);

    $this->artisan('payouts:generate');
    expect(Payout::count())->toBe(1);

    $this->artisan('payouts:generate');
    expect(Payout::count())->toBe(1);
});

test('amount_cents = base_price_cents_snapshot x percentual conforme satisfaction_score', function (?float $score, int $basePrice, int $expected) {
    $carWash = CarWash::factory()->approved()->create(['satisfaction_score' => $score]);
    completedRedemption($carWash, $basePrice);

    $this->artisan('payouts:generate');

    expect(PayoutItem::sole()->amount_cents)->toBe($expected);
})->with([
    'sem avaliacao (null) -> 70%' => [null, 10000, 7000],
    'nota < 70 -> 60%' => [65.0, 10000, 6000],
    'nota entre 70 e 90 -> 70%' => [80.0, 10000, 7000],
    'nota exatamente 70 -> 70%' => [70.0, 10000, 7000],
    'nota exatamente 90 -> 70%' => [90.0, 10000, 7000],
    'nota > 90 -> 75%' => [95.0, 10000, 7500],
]);

test('payouts.total_amount_cents bate com a soma dos amount_cents dos items', function () {
    $carWash = CarWash::factory()->approved()->create(['satisfaction_score' => 80]);
    completedRedemption($carWash, 2000);
    completedRedemption($carWash, 3000);
    completedRedemption($carWash, 5000);

    $this->artisan('payouts:generate');

    $payout = Payout::sole();
    $sum = (int) PayoutItem::where('payout_id', $payout->id)->sum('amount_cents');

    expect($payout->total_amount_cents)->toBe($sum);
});

test('lavagem que ja entrou num payout nao entra de novo', function () {
    $carWash = CarWash::factory()->approved()->create();
    $redemption = completedRedemption($carWash, 2000);

    $this->artisan('payouts:generate');
    $firstPayoutId = $redemption->fresh()->payout_item_id;

    completedRedemption($carWash, 3000);
    $this->artisan('payouts:generate');

    expect($redemption->fresh()->payout_item_id)->toBe($firstPayoutId)
        ->and(Payout::count())->toBe(2);
});

test('lavagens de dois lava-rapidos diferentes geram payouts separados', function () {
    $carWashA = CarWash::factory()->approved()->create();
    $carWashB = CarWash::factory()->approved()->create();
    completedRedemption($carWashA, 2000);
    completedRedemption($carWashB, 3000);

    $this->artisan('payouts:generate');

    expect(Payout::count())->toBe(2)
        ->and(Payout::where('car_wash_id', $carWashA->id)->exists())->toBeTrue()
        ->and(Payout::where('car_wash_id', $carWashB->id)->exists())->toBeTrue();
});
