<?php

use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use Database\Seeders\DatabaseSeeder;

// Ver task-9, seção 3.2, e task-13, seção 2.5.

function cancellationAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

function pendingCancellationRequest(WashRedemption $redemption, User $requester): CancellationRequest
{
    return CancellationRequest::factory()->create([
        'requestable_type' => WashRedemption::class,
        'requestable_id' => $redemption->id,
        'requested_by_user_id' => $requester->id,
    ]);
}

test('aprovar cancelamento de lavagem sem payout: cancela e devolve a cota do ciclo atual', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $subscriber = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($subscriber)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_total' => 4,
        'quota_used' => 2,
    ]);
    $redemption = WashRedemption::factory()->completed()->create(['subscription_cycle_id' => $cycle->id]);
    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)
        ->post(route('cancellation-requests.approve', $request))
        ->assertRedirect(route('cancellation-requests.index'));

    expect($redemption->fresh()->status)->toBe('canceled')
        ->and($cycle->fresh()->quota_used)->toBe(1)
        ->and($request->fresh()->status)->toBe('approved')
        ->and($request->fresh()->resolved_by_user_id)->toBe($admin->id)
        ->and($request->fresh()->resolved_at)->not->toBeNull();
});

test('nao devolve cota se o ciclo ja foi renovado (nao e mais o atual)', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $subscriber = User::factory()->create();
    $subscription = Subscription::factory()->for($subscriber)->active()->create();
    $oldCycle = SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_used' => 3,
        'period_start' => now()->subMonth(),
    ]);
    // Ciclo mais novo já existe — o antigo não é mais o atual.
    SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'period_start' => now(),
    ]);
    $redemption = WashRedemption::factory()->completed()->create(['subscription_cycle_id' => $oldCycle->id]);
    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)->post(route('cancellation-requests.approve', $request));

    expect($oldCycle->fresh()->quota_used)->toBe(3);
});

test('aprovar cancelamento de lavagem ja em payout pending remove o item e recalcula o total', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $carWash = CarWash::factory()->approved()->create();
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create(['quota_used' => 1]);
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $payout = Payout::factory()->create(['car_wash_id' => $carWash->id, 'status' => 'pending', 'total_amount_cents' => 5000]);
    $otherItem = PayoutItem::factory()->create(['payout_id' => $payout->id, 'amount_cents' => 3000]);
    $item = PayoutItem::factory()->create(['payout_id' => $payout->id, 'amount_cents' => 2000, 'wash_redemption_id' => $redemption->id]);
    $redemption->update(['payout_item_id' => $item->id]);

    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)->post(route('cancellation-requests.approve', $request));

    expect(PayoutItem::whereKey($item->id)->exists())->toBeFalse()
        ->and($payout->fresh()->total_amount_cents)->toBe(3000)
        ->and($redemption->fresh()->payout_item_id)->toBeNull();
});

test('aprovar cancelamento de lavagem em payout ja pago nao estorna automaticamente', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $carWash = CarWash::factory()->approved()->create();
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create();
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $payout = Payout::factory()->create(['car_wash_id' => $carWash->id, 'status' => 'paid', 'total_amount_cents' => 2000]);
    $item = PayoutItem::factory()->create(['payout_id' => $payout->id, 'amount_cents' => 2000, 'wash_redemption_id' => $redemption->id]);
    $redemption->update(['payout_item_id' => $item->id]);

    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)->post(route('cancellation-requests.approve', $request));

    expect($redemption->fresh()->status)->toBe('canceled')
        // O item/payout NÃO são mexidos — pendência manual.
        ->and(PayoutItem::whereKey($item->id)->exists())->toBeTrue()
        ->and($payout->fresh()->total_amount_cents)->toBe(2000)
        ->and($redemption->fresh()->payout_item_id)->toBe($item->id);
});

test('rejeitar cancelamento nao muda a wash_redemption', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create(['quota_used' => 2]);
    $redemption = WashRedemption::factory()->completed()->create(['subscription_cycle_id' => $cycle->id]);
    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)
        ->post(route('cancellation-requests.reject', $request))
        ->assertRedirect(route('cancellation-requests.index'));

    expect($redemption->fresh()->status)->toBe('completed')
        ->and($cycle->fresh()->quota_used)->toBe(2)
        ->and($request->fresh()->status)->toBe('rejected');
});

test('lavagem sem payout_item ainda simplesmente nao entra no proximo lote', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = cancellationAdmin();
    $subscriber = User::factory()->create();
    $cycle = SubscriptionCycle::factory()->create();
    $redemption = WashRedemption::factory()->completed()->create(['subscription_cycle_id' => $cycle->id]);
    $request = pendingCancellationRequest($redemption, $subscriber);

    $this->actingAs($admin)->post(route('cancellation-requests.approve', $request));

    $this->artisan('payouts:generate');

    expect(Payout::count())->toBe(0);
});
