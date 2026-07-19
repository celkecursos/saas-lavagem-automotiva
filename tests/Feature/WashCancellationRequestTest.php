<?php

use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;

// Ver task-8, seção 2, passo 8.

function completedRedemptionForCancellation(): array
{
    $carWash = CarWash::factory()->approved()->create();
    $subscriber = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($subscriber)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    return [$carWash, $subscriber, $redemption];
}

test('assinante solicita cancelamento de lavagem completed: cria pending, nada muda na wash_redemption', function () {
    [, $subscriber, $redemption] = completedRedemptionForCancellation();

    $this->actingAs($subscriber)
        ->post(route('wash.request-cancellation', $redemption), ['reason' => 'Confirmaram errado'])
        ->assertRedirect(route('wash.choose'));

    $request = CancellationRequest::sole();

    expect($request->requestable_type)->toBe(WashRedemption::class)
        ->and($request->requestable_id)->toBe($redemption->id)
        ->and($request->requested_by_user_id)->toBe($subscriber->id)
        ->and($request->status)->toBe('pending')
        ->and($redemption->fresh()->status)->toBe('completed');
});

test('funcionario do lava-rapido tambem pode solicitar cancelamento', function () {
    [$carWash, , $redemption] = completedRedemptionForCancellation();
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.request-cancellation', $redemption), ['reason' => 'Confirmei a pessoa errada'])
        ->assertRedirect(route('panel.washes.index'));

    expect(CancellationRequest::sole()->requested_by_user_id)->toBe($employee->id);
});

test('nao pode solicitar cancelamento de lavagem ainda nao completed', function () {
    $carWash = CarWash::factory()->approved()->create();
    $subscriber = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($subscriber)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $redemption = WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'status' => 'requested',
    ]);

    $this->actingAs($subscriber)
        ->post(route('wash.request-cancellation', $redemption), ['reason' => 'x'])
        ->assertSessionHas('error');

    expect(CancellationRequest::count())->toBe(0);
});

test('nao permite duas solicitacoes pending para a mesma lavagem', function () {
    [, $subscriber, $redemption] = completedRedemptionForCancellation();

    $this->actingAs($subscriber)->post(route('wash.request-cancellation', $redemption), ['reason' => 'Primeira']);
    $this->actingAs($subscriber)
        ->post(route('wash.request-cancellation', $redemption), ['reason' => 'Segunda'])
        ->assertSessionHas('error');

    expect(CancellationRequest::count())->toBe(1);
});

test('funcionario de outro lava-rapido nao pode solicitar cancelamento', function () {
    [, , $redemption] = completedRedemptionForCancellation();
    $otherCarWash = CarWash::factory()->approved()->create();
    $employee = User::factory()->create();
    $otherCarWash->users()->attach($employee->id, ['role' => 'employee']);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $otherCarWash->id])
        ->post(route('panel.washes.request-cancellation', $redemption), ['reason' => 'x'])
        ->assertForbidden();
});
