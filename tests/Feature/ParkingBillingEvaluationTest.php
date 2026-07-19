<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingBillingSetting;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\SubscriptionCycle;
use App\Models\WashRedemption;
use Illuminate\Support\Carbon;

// Ver task-10, seção 5, e task-13, seção 2.6.

function billingCarWash(int $totalSpots): CarWash
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    ParkingLot::factory()->create(['car_wash_id' => $carWash->id, 'total_spots' => $totalSpots]);

    return $carWash;
}

function completedWashesForBilling(CarWash $carWash, int $count, Carbon $when): void
{
    for ($i = 0; $i < $count; $i++) {
        $cycle = SubscriptionCycle::factory()->create();
        WashRedemption::factory()->completed()->create([
            'subscription_cycle_id' => $cycle->id,
            'car_wash_id' => $carWash->id,
            'redeemed_at' => $when,
        ]);
    }
}

function closedParkingSessionsForBilling(CarWash $carWash, int $count, Carbon $when, int $amountCents = 1000): void
{
    $lot = $carWash->parkingLots()->first();
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    for ($i = 0; $i < $count; $i++) {
        ParkingSession::factory()->closed()->create([
            'parking_lot_id' => $lot->id,
            'parking_rate_id' => $rate->id,
            'exit_at' => $when,
            'amount_charged_cents' => $amountCents,
        ]);
    }
}

test('wash_count >= total_spots gera charge free, sem order', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $carWash = billingCarWash(totalSpots: 3);
    completedWashesForBilling($carWash, 3, now()->subMonthNoOverflow());

    $this->artisan('parking-billing:evaluate')->assertSuccessful();

    $charge = ParkingBillingCharge::sole();
    expect($charge->is_free)->toBeTrue()
        ->and($charge->status)->toBe('free')
        ->and($charge->order_id)->toBeNull();
});

test('wash_count EXATAMENTE IGUAL a total_spots tambem e free (regra e >=, nao >)', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $carWash = billingCarWash(totalSpots: 5);
    completedWashesForBilling($carWash, 5, now()->subMonthNoOverflow());

    $this->artisan('parking-billing:evaluate');

    expect(ParkingBillingCharge::sole()->is_free)->toBeTrue();
});

test('wash_count menor que total_spots gera charge pending com fee_amount_cents calculado', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $carWash = billingCarWash(totalSpots: 10);
    completedWashesForBilling($carWash, 2, now()->subMonthNoOverflow());
    closedParkingSessionsForBilling($carWash, 3, now()->subMonthNoOverflow(), amountCents: 1000);

    $this->artisan('parking-billing:evaluate');

    $charge = ParkingBillingCharge::sole();
    expect($charge->is_free)->toBeFalse()
        ->and($charge->status)->toBe('pending')
        // 3 sessões × 1000 centavos = 3000; 10% (default) = 300.
        ->and($charge->fee_amount_cents)->toBe(300)
        ->and((float) $charge->fee_percentage_applied)->toBe(10.0);
});

test('parking_sessions_count muito acima do plausivel marca flagged_for_review', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    // total_spots=1, max_turns_per_day_per_spot padrão 6, fevereiro
    // (mês anterior a março) tem 28 dias em 2026 -> teto = 1×6×28=168.
    $carWash = billingCarWash(totalSpots: 1);
    closedParkingSessionsForBilling($carWash, 200, now()->subMonthNoOverflow());

    $this->artisan('parking-billing:evaluate');

    expect(ParkingBillingCharge::sole()->flagged_for_review)->toBeTrue();
});

test('volume plausivel nao marca flagged_for_review', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $carWash = billingCarWash(totalSpots: 10);
    closedParkingSessionsForBilling($carWash, 5, now()->subMonthNoOverflow());

    $this->artisan('parking-billing:evaluate');

    expect(ParkingBillingCharge::sole()->flagged_for_review)->toBeFalse();
});

test('rodar o comando 2x no mesmo periodo nao duplica charges', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    $carWash = billingCarWash(totalSpots: 5);
    completedWashesForBilling($carWash, 1, now()->subMonthNoOverflow());

    $this->artisan('parking-billing:evaluate');
    $this->artisan('parking-billing:evaluate');

    expect(ParkingBillingCharge::where('car_wash_id', $carWash->id)->count())->toBe(1);
});

test('lava-rapido sem estacionamento ativo nao gera charge', function () {
    Carbon::setTestNow('2026-02-15 12:00:00');
    CarWash::factory()->approved()->create();

    $this->artisan('parking-billing:evaluate');

    expect(ParkingBillingCharge::count())->toBe(0);
});
