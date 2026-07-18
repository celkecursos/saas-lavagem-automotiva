<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionCanceled;
use App\Notifications\SubscriptionConfirmed;
use App\Notifications\SubscriptionRenewalFailed;
use App\Notifications\SubscriptionRenewed;
use App\Services\Payment\PagSeguroGateway;
use App\Services\Subscription\SubscriptionActivator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

// Ver task-7, seção 7 — notificações do ciclo de vida da assinatura.

test('ativacao dispara SubscriptionConfirmed', function () {
    Notification::fake();
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    Notification::assertSentTo($user, SubscriptionConfirmed::class);
});

test('renovacao aprovada dispara SubscriptionRenewed; falha dispara SubscriptionRenewalFailed', function () {
    Notification::fake();
    $type = PaymentGatewayType::factory()->create(['slug' => 'pagseguro', 'service_class' => PagSeguroGateway::class]);
    $gateway = PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'lifecycle-token'],
    ]);

    $userOk = User::factory()->create();
    $subscriptionOk = Subscription::factory()->for($userOk)->active()->create(['current_period_end' => now()->subDay()]);
    PaymentMethodToken::create(['user_id' => $userOk->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_OK']);

    $userFail = User::factory()->create();
    $subscriptionFail = Subscription::factory()->for($userFail)->active()->create(['current_period_end' => now()->subDay()]);
    PaymentMethodToken::create(['user_id' => $userFail->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_FAIL']);

    Http::fake(function ($request) {
        $card = $request->data()['charges'][0]['payment_method']['card']['id'] ?? null;
        $status = $card === 'CARD_OK' ? 'PAID' : 'DECLINED';

        return Http::response(['id' => 'ORDE', 'charges' => [['id' => 'CHAR', 'status' => $status]]], 201);
    });

    $this->artisan('subscriptions:renew');

    Notification::assertSentTo($userOk, SubscriptionRenewed::class);
    Notification::assertSentTo($userFail, SubscriptionRenewalFailed::class);
});

test('cancelamento automatico apos a carencia dispara SubscriptionCanceled', function () {
    Notification::fake();
    $type = PaymentGatewayType::factory()->create(['slug' => 'pagseguro', 'service_class' => PagSeguroGateway::class]);
    $gateway = PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'cancel-token'],
    ]);
    Http::fake(['*' => Http::response(['id' => 'ORDE', 'charges' => [['id' => 'CHAR', 'status' => 'DECLINED']]], 201)]);

    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create([
        'status' => 'past_due',
        'current_period_end' => now()->subDays(5),
    ]);
    PaymentMethodToken::create(['user_id' => $user->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_X']);

    $this->artisan('subscriptions:renew');

    Notification::assertSentTo($user, SubscriptionCanceled::class);
});
