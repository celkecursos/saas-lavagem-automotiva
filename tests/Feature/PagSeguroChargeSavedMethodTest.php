<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// Ver task-4, seção 5 (recorrência manual, commit 7), e task-13, 2.1/2.3.
// Validado também contra o sandbox real: SUBSEQUENT com card.id => PAID.

function makeChargeScenario(array $charge): array
{
    Http::fake([
        'sandbox.api.pagseguro.com/orders' => Http::response([
            'id' => 'ORDE_FAKE',
            'charges' => [$charge],
        ], 201),
    ]);

    $type = PaymentGatewayType::factory()->create([
        'slug' => 'pagseguro',
        'service_class' => PagSeguroGateway::class,
    ]);
    $model = PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'sandbox-token-abc'],
    ]);

    $user = User::factory()->create();
    $order = Order::factory()->subsequent()->for($user)->create();
    $method = PaymentMethodToken::create([
        'user_id' => $user->id,
        'payment_gateway_id' => $model->id,
        'token' => 'CARD_123',
    ]);

    return [new PagSeguroGateway($model), $order, $method];
}

test('chargeSavedMethod cobra com card.id + SUBSEQUENT, sem checkout', function () {
    [$gateway, $order, $method] = makeChargeScenario(['id' => 'CHAR_RENOVACAO', 'status' => 'PAID']);

    $result = $gateway->chargeSavedMethod($order, $method);

    expect($result->status)->toBe('paid')
        ->and($result->externalReference)->toBe('CHAR_RENOVACAO');

    Http::assertSent(function ($request) {
        $card = $request->data()['charges'][0]['payment_method']['card'];

        return $card['id'] === 'CARD_123'
            && $card['recurring']['type'] === 'SUBSEQUENT'
            && ! array_key_exists('encrypted', $card);
    });
});

test('chargeSavedMethod recusado devolve failureReason pro e-mail de past_due', function () {
    [$gateway, $order, $method] = makeChargeScenario([
        'id' => 'CHAR_FALHA',
        'status' => 'DECLINED',
        'payment_response' => ['message' => 'Cartão expirado'],
    ]);

    $result = $gateway->chargeSavedMethod($order, $method);

    expect($result->status)->toBe('failed')
        ->and($result->failureReason)->toBe('Cartão expirado');
});
