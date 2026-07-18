<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentWebhookEvent;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Support\Facades\Route;

// Ver task-13, seção 2.1 — webhook: assinatura inválida, idempotência
// real (não só o unique constraint isolado).

function webhookGateway(): PaymentGateway
{
    $type = PaymentGatewayType::factory()->create([
        'slug' => 'pagseguro',
        'service_class' => PagSeguroGateway::class,
    ]);

    return PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'webhook-token-abc'],
    ]);
}

function signedWebhookPayload(string $token, array $payload): array
{
    $body = json_encode($payload);
    $signature = hash('sha256', "{$token}-{$body}");

    return [$body, $signature];
}

test('webhook com assinatura invalida e rejeitado (401) e order continua pending', function () {
    webhookGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'status' => 'pending',
    ]);

    $response = $this->call('POST', '/webhooks/pagseguro', [], [], [], [
        'HTTP_X_AUTHENTICITY_TOKEN' => 'assinatura-errada',
        'CONTENT_TYPE' => 'application/json',
    ], json_encode(['reference_id' => "order-{$order->id}", 'charges' => [['id' => 'CHAR_X', 'status' => 'PAID']]]));

    $response->assertStatus(401);
    expect($order->fresh()->status)->toBe('pending');
});

test('webhook valido marca a order paid e ativa a assinatura', function () {
    $gateway = webhookGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payment_gateway_id' => $gateway->id,
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'status' => 'pending',
    ]);

    [$body, $signature] = signedWebhookPayload('webhook-token-abc', [
        'reference_id' => "order-{$order->id}",
        'charges' => [['id' => 'CHAR_OK', 'status' => 'PAID']],
    ]);

    $response = $this->call('POST', '/webhooks/pagseguro', [], [], [], [
        'HTTP_X_AUTHENTICITY_TOKEN' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertOk();

    expect($order->fresh()->status)->toBe('paid')
        ->and($subscription->fresh()->status)->toBe('active');

    expect(SubscriptionCycle::where('subscription_id', $subscription->id)->count())->toBe(1);
});

test('reenviar o MESMO webhook nao duplica nada (idempotencia real)', function () {
    $gateway = webhookGateway();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->create();
    $order = Order::factory()->initial()->for($user)->create([
        'payment_gateway_id' => $gateway->id,
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'status' => 'pending',
    ]);

    [$body, $signature] = signedWebhookPayload('webhook-token-abc', [
        'reference_id' => "order-{$order->id}",
        'charges' => [['id' => 'CHAR_DUP', 'status' => 'PAID']],
    ]);

    $headers = [
        'HTTP_X_AUTHENTICITY_TOKEN' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ];

    $this->call('POST', '/webhooks/pagseguro', [], [], [], $headers, $body)->assertOk();
    $cyclesAfterFirst = SubscriptionCycle::where('subscription_id', $subscription->id)->count();

    // Reenvio do MESMO evento (provedor reenvia em caso de timeout).
    $this->call('POST', '/webhooks/pagseguro', [], [], [], $headers, $body)->assertOk();

    expect(PaymentWebhookEvent::count())->toBe(1)
        ->and(SubscriptionCycle::where('subscription_id', $subscription->id)->count())->toBe($cyclesAfterFirst);
});
