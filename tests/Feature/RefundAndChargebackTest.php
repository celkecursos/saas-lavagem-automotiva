<?php

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\ParkingBillingCharge;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\Plan;
use App\Models\RefundSetting;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ChargebackReceived;
use App\Services\Payment\PagSeguroGateway;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

// Ver task-21, e task-13.

function refundGateway(): PaymentGateway
{
    // firstOrCreate: alguns testes seedam o DatabaseSeeder (que já
    // cria o type 'pagseguro' via PaymentGatewayTypeSeeder) antes de
    // chamar isso.
    $type = PaymentGatewayType::firstOrCreate(
        ['slug' => 'pagseguro'],
        ['name' => 'PagSeguro / PagBank', 'service_class' => PagSeguroGateway::class],
    );

    return PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'refund-token-abc'],
    ]);
}

function paidSubscriptionOrder(PaymentGateway $gateway, array $overrides = []): array
{
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $order = Order::factory()->initial()->for($user)->create(array_merge([
        'payment_gateway_id' => $gateway->id,
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'status' => 'paid',
        'external_reference' => 'CHAR_REFUNDABLE',
        'paid_at' => now(),
    ], $overrides));

    return [$user, $subscription, $order];
}

function refundAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('assinante solicita reembolso self-service dentro dos 7 dias e a assinatura e cancelada na hora', function () {
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['status' => 'CANCELED'])]);
    $gateway = refundGateway();
    [$user, $subscription, $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(3)]);

    $this->actingAs($user)
        ->post(route('order.request-refund', $order), ['reason' => 'Não gostei do serviço'])
        ->assertRedirect(route('order.show', $order));

    $refundRequest = OrderRefundRequest::sole();

    expect($refundRequest->status)->toBe('processed')
        ->and($refundRequest->initiated_by)->toBe('self_service')
        ->and($order->fresh()->status)->toBe('refunded')
        ->and($subscription->fresh()->status)->toBe('canceled')
        ->and($subscription->fresh()->canceled_reason)->toBe('refund')
        ->and($subscription->fresh()->canceled_at)->not->toBeNull();
});

test('reembolso self-service fora da janela de 7 dias e bloqueado sem janela estendida', function () {
    $gateway = refundGateway();
    [$user, , $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(10)]);

    $this->actingAs($user)
        ->post(route('order.request-refund', $order), ['reason' => 'Fora do prazo'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(OrderRefundRequest::count())->toBe(0)
        ->and($order->fresh()->status)->toBe('paid');
});

test('janela estendida habilitada libera self-service depois do dia 7', function () {
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['status' => 'CANCELED'])]);
    RefundSetting::current()->update(['extended_self_service_enabled' => true, 'extended_self_service_until_days' => 15]);
    $gateway = refundGateway();
    [$user, , $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(10)]);

    $this->actingAs($user)
        ->post(route('order.request-refund', $order), ['reason' => 'Dentro da janela estendida'])
        ->assertRedirect(route('order.show', $order));

    expect(OrderRefundRequest::sole()->status)->toBe('processed');
});

test('nao pode solicitar reembolso duas vezes pro mesmo pedido', function () {
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['status' => 'CANCELED'])]);
    $gateway = refundGateway();
    [$user, , $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDay()]);

    $this->actingAs($user)->post(route('order.request-refund', $order), ['reason' => 'Primeiro pedido']);

    $this->actingAs($user)
        ->post(route('order.request-refund', $order), ['reason' => 'Segundo pedido'])
        ->assertSessionHas('error');

    expect(OrderRefundRequest::count())->toBe(1);
});

test('gateway que nao processa o estorno via API vira failed_manual, sem cancelar a assinatura ainda', function () {
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['error' => 'x'], 400)]);
    $gateway = refundGateway();
    [$user, $subscription, $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDay()]);

    $this->actingAs($user)->post(route('order.request-refund', $order), ['reason' => 'Motivo']);

    expect(OrderRefundRequest::sole()->status)->toBe('failed_manual')
        ->and($order->fresh()->status)->toBe('paid')
        ->and($subscription->fresh()->status)->toBe('active');
});

test('admin confirma manualmente um failed_manual: processa e revoga o acesso', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = refundAdmin();
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['error' => 'x'], 400)]);
    $gateway = refundGateway();
    [$user, $subscription, $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDay()]);
    $this->actingAs($user)->post(route('order.request-refund', $order), ['reason' => 'Motivo']);
    $refundRequest = OrderRefundRequest::sole();

    $this->actingAs($admin)
        ->post(route('order-refund-requests.mark-processed', $refundRequest))
        ->assertRedirect(route('order-refund-requests.index'));

    expect($refundRequest->fresh()->status)->toBe('processed')
        ->and($order->fresh()->status)->toBe('refunded')
        ->and($subscription->fresh()->status)->toBe('canceled')
        ->and($subscription->fresh()->canceled_reason)->toBe('refund');
});

test('admin solicita reembolso em nome do assinante fora da janela', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = refundAdmin();
    Http::fake(['sandbox.api.pagseguro.com/charges/CHAR_REFUNDABLE/cancel' => Http::response(['status' => 'CANCELED'])]);
    $gateway = refundGateway();
    [, $subscription, $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(30)]);

    $this->actingAs($admin)
        ->post(route('orders.refund', $order), ['reason' => 'Suporte autorizou'])
        ->assertRedirect(route('orders.show', $order));

    $refundRequest = OrderRefundRequest::sole();
    expect($refundRequest->initiated_by)->toBe('admin')
        ->and($refundRequest->status)->toBe('processed')
        ->and($subscription->fresh()->status)->toBe('canceled');
});

test('admin sem a permission orders.refund toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $gateway = refundGateway();
    [, , $order] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDay()]);

    $this->actingAs($user)
        ->post(route('orders.refund', $order), ['reason' => 'x'])
        ->assertForbidden();
});

test('webhook de chargeback cancela a assinatura na hora e notifica os admins com orders.refund', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = refundAdmin();
    Notification::fake();
    $gateway = refundGateway();
    [$user, $subscription, $order] = paidSubscriptionOrder($gateway);

    $body = json_encode(['reference_id' => "order-{$order->id}", 'charges' => [['id' => 'CHAR_CB', 'status' => 'CHARGED_BACK']]]);
    $signature = hash('sha256', "refund-token-abc-{$body}");

    $this->call('POST', '/webhooks/pagseguro', [], [], [], [
        'HTTP_X_AUTHENTICITY_TOKEN' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk();

    expect($order->fresh()->status)->toBe('chargeback')
        ->and($subscription->fresh()->status)->toBe('canceled')
        ->and($subscription->fresh()->canceled_reason)->toBe('chargeback');

    Notification::assertSentTo($admin, ChargebackReceived::class);
});

test('webhook de chargeback numa cobranca de estacionamento so marca a charge, sem tentar cancelar assinatura', function () {
    $gateway = refundGateway();
    $carWash = \App\Models\CarWash::factory()->approved()->create();
    $owner = User::factory()->create();
    $charge = ParkingBillingCharge::factory()->create(['car_wash_id' => $carWash->id, 'status' => 'paid']);
    $order = Order::factory()->for($owner)->create([
        'payment_gateway_id' => $gateway->id,
        'payable_type' => ParkingBillingCharge::class,
        'payable_id' => $charge->id,
        'status' => 'paid',
    ]);
    $charge->update(['order_id' => $order->id]);

    $body = json_encode(['reference_id' => "order-{$order->id}", 'charges' => [['id' => 'CHAR_CB2', 'status' => 'CHARGED_BACK']]]);
    $signature = hash('sha256', "refund-token-abc-{$body}");

    $this->call('POST', '/webhooks/pagseguro', [], [], [], [
        'HTTP_X_AUTHENTICITY_TOKEN' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk();

    expect($order->fresh()->status)->toBe('chargeback')
        ->and($charge->fresh()->status)->toBe('chargeback');
});

test('admin atualiza a janela estendida de reembolso, fica registrado em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = refundAdmin();
    $settings = RefundSetting::current();

    $this->actingAs($admin)
        ->put(route('refund-settings.update'), [
            'extended_self_service_enabled' => '1',
            'extended_self_service_until_days' => 20,
        ])
        ->assertRedirect(route('refund-settings.edit'));

    $settings->refresh();

    expect($settings->extended_self_service_enabled)->toBeTrue()
        ->and($settings->extended_self_service_until_days)->toBe(20);

    expect(\Illuminate\Support\Facades\DB::table('audits')
        ->where('auditable_type', RefundSetting::class)
        ->where('auditable_id', $settings->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('fila de reembolsos manuais lista so os failed_manual', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = refundAdmin();
    $pending = OrderRefundRequest::factory()->failedManual()->create();
    OrderRefundRequest::factory()->processed()->create();

    $response = $this->actingAs($admin)->get(route('order-refund-requests.index'));

    $response->assertOk()->assertSee("Pedido #{$pending->order->id}");
});

test('tela do pedido mostra o botao de solicitar reembolso so quando elegivel', function () {
    $gateway = refundGateway();
    [$user, , $orderElegivel] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(2)]);
    [, , $orderForaDaJanela] = paidSubscriptionOrder($gateway, ['paid_at' => now()->subDays(30)]);
    $orderForaDaJanela->update(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('order.show', $orderElegivel))
        ->assertOk()->assertSee('Solicitar reembolso');

    $this->actingAs($user)->get(route('order.show', $orderForaDaJanela))
        ->assertOk()->assertDontSee('Solicitar reembolso');
});

test('assinante nao acessa pedido de outro usuario', function () {
    $gateway = refundGateway();
    [, , $order] = paidSubscriptionOrder($gateway);
    $outro = User::factory()->create();

    $this->actingAs($outro)->get(route('order.show', $order))->assertForbidden();
});

test('historico de pedidos aparece em /assinatura com link pro pedido', function () {
    $gateway = refundGateway();
    [$user, $subscription, $order] = paidSubscriptionOrder($gateway);

    $this->actingAs($user)->get(route('subscription.show'))
        ->assertOk()
        ->assertSee("Pedido #{$order->id}");
});
