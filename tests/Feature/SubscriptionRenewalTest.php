<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Support\Facades\Http;

// Ver task-13, seção 2.3 — subscriptions:renew (task-7, seção 4).

function renewalGateway(): PaymentGateway
{
    $type = PaymentGatewayType::factory()->create([
        'slug' => 'pagseguro',
        'service_class' => PagSeguroGateway::class,
    ]);

    return PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'renew-token-abc'],
    ]);
}

function fakeRenewalCharge(array $charge): void
{
    Http::fake([
        'sandbox.api.pagseguro.com/orders' => Http::response([
            'id' => 'ORDE_FAKE',
            'charges' => [$charge],
        ], 201),
    ]);
}

test('renovacao usa chargeSavedMethod (SUBSEQUENT), nunca createCheckout', function () {
    fakeRenewalCharge(['id' => 'CHAR_RENOVA', 'status' => 'PAID']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
        'status' => 'active',
        'current_period_start' => now()->subMonth(),
        'current_period_end' => now()->subDay(),
    ]);
    PaymentMethodToken::create([
        'user_id' => $user->id,
        'payment_gateway_id' => $gateway->id,
        'token' => 'CARD_SALVO',
    ]);

    $this->artisan('subscriptions:renew')->assertSuccessful();

    $order = Order::sole();

    expect($order->recurring_type)->toBe('subsequent')
        ->and($order->status)->toBe('paid');

    Http::assertSent(function ($request) {
        $card = $request->data()['charges'][0]['payment_method']['card'];

        return $card['id'] === 'CARD_SALVO'
            && $card['recurring']['type'] === 'SUBSEQUENT'
            && ! array_key_exists('encrypted', $card);
    });
});

test('renovacao aprovada cria subscription_cycles novo com quota_used=0', function () {
    fakeRenewalCharge(['id' => 'CHAR_1', 'status' => 'PAID']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 5, 'rollover_quota' => false]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_1']);

    $this->artisan('subscriptions:renew');

    $subscription->refresh();
    $cycle = SubscriptionCycle::where('subscription_id', $subscription->id)->sole();

    expect($subscription->status)->toBe('active')
        ->and($cycle->quota_total)->toBe(5)
        ->and($cycle->quota_used)->toBe(0);
});

test('rollover_quota=true soma o saldo nao usado do ciclo anterior', function () {
    fakeRenewalCharge(['id' => 'CHAR_2', 'status' => 'PAID']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4, 'rollover_quota' => true]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);
    SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_total' => 4,
        'quota_used' => 1,
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_1']);

    $this->artisan('subscriptions:renew');

    $newCycle = SubscriptionCycle::where('subscription_id', $subscription->id)->latest('id')->first();

    // 4 do plano + 3 não usados do ciclo anterior (4 - 1).
    expect($newCycle->quota_total)->toBe(7);
});

test('rollover_quota=false zera, ignora saldo do ciclo anterior', function () {
    fakeRenewalCharge(['id' => 'CHAR_3', 'status' => 'PAID']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4, 'rollover_quota' => false]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create([
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);
    SubscriptionCycle::factory()->create([
        'subscription_id' => $subscription->id,
        'quota_total' => 4,
        'quota_used' => 1,
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_1']);

    $this->artisan('subscriptions:renew');

    $newCycle = SubscriptionCycle::where('subscription_id', $subscription->id)->latest('id')->first();

    expect($newCycle->quota_total)->toBe(4);
});

test('falha de renovacao vira past_due, sem token nem tenta cobrar', function () {
    renewalGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);
    // Sem payment_method_tokens pro gateway ativo.

    $this->artisan('subscriptions:renew');

    expect($subscription->fresh()->status)->toBe('past_due')
        ->and(Order::count())->toBe(0);
});

test('assinante sem token pro gateway ATUALMENTE ativo (trocou de gateway) falha sem tentar cobrar', function () {
    $oldGateway = PaymentGateway::factory()->create(); // inativo
    $newGateway = renewalGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'status' => 'active',
        'current_period_end' => now()->subDay(),
    ]);
    // Token existe, mas é do gateway ANTIGO, não do atualmente ativo.
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $oldGateway->id, 'token' => 'CARD_ANTIGO']);

    $this->artisan('subscriptions:renew');

    expect($subscription->fresh()->status)->toBe('past_due')
        ->and(Order::count())->toBe(0);
});

test('falha apos a carencia cancela automaticamente', function () {
    fakeRenewalCharge(['id' => 'CHAR_FAIL', 'status' => 'DECLINED']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'status' => 'past_due',
        // Vencido há mais de 3 dias de carência.
        'current_period_end' => now()->subDays(5),
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_1']);

    $this->artisan('subscriptions:renew');

    expect($subscription->fresh()->status)->toBe('canceled');
});

test('falha dentro da carencia permanece past_due, tenta de novo amanha', function () {
    fakeRenewalCharge(['id' => 'CHAR_FAIL2', 'status' => 'DECLINED']);
    $gateway = renewalGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'status' => 'past_due',
        'current_period_end' => now()->subDay(),
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_1']);

    $this->artisan('subscriptions:renew');

    expect($subscription->fresh()->status)->toBe('past_due');
});

test('nao mexe em assinaturas ainda nao vencidas', function () {
    renewalGateway();
    $subscription = Subscription::factory()->create([
        'status' => 'active',
        'current_period_end' => now()->addDays(10),
    ]);

    $this->artisan('subscriptions:renew');

    expect($subscription->fresh()->status)->toBe('active')
        ->and(Order::count())->toBe(0);
});
