<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// Ver task-4, seções 5 e 5.2, e task-13, seção 2.1. As chamadas HTTP
// são mockadas aqui; o fluxo real foi validado no sandbox antes do push.

function makePagSeguro(): array
{
    $type = PaymentGatewayType::factory()->create([
        'slug' => 'pagseguro',
        'service_class' => PagSeguroGateway::class,
    ]);

    $model = PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'sandbox-token-abc'],
        'sandbox_mode' => true,
    ]);

    return [new PagSeguroGateway($model), $model];
}

function fakeOrdersResponse(array $charge): void
{
    Http::fake([
        'sandbox.api.pagseguro.com/orders' => Http::response([
            'id' => 'ORDE_FAKE',
            'reference_id' => 'order-1',
            'charges' => [$charge],
        ], 201),
    ]);
}

test('createCheckout INITIAL aprovado tokeniza e salva o cartao', function () {
    fakeOrdersResponse([
        'id' => 'CHAR_APROVADA',
        'status' => 'PAID',
        'payment_method' => ['card' => ['id' => 'CARD_123', 'brand' => 'VISA', 'last_digits' => '2097']],
    ]);

    [$gateway, $model] = makePagSeguro();
    $order = Order::factory()->initial()->create();

    $gateway->setEncryptedCard('blob-criptografado-do-browser');
    $result = $gateway->createCheckout($order);

    expect($result->embeddedData['status'])->toBe('paid')
        ->and($result->externalReference)->toBe('CHAR_APROVADA');

    // card.id devolvido vira payment_method_tokens (task-4, seção 5).
    $token = PaymentMethodToken::sole();
    expect($token->token)->toBe('CARD_123')
        ->and($token->user_id)->toBe($order->user_id)
        ->and($token->payment_gateway_id)->toBe($model->id)
        ->and($token->last_four)->toBe('2097');

    Http::assertSent(function ($request) {
        $card = $request->data()['charges'][0]['payment_method']['card'];

        return $card['encrypted'] === 'blob-criptografado-do-browser'
            && $card['store'] === true
            && $card['recurring']['type'] === 'INITIAL';
    });
});

test('createCheckout recusado nao cria token e devolve o motivo', function () {
    fakeOrdersResponse([
        'id' => 'CHAR_RECUSADA',
        'status' => 'DECLINED',
        'payment_response' => ['message' => 'Cartão recusado pela emissora'],
    ]);

    [$gateway] = makePagSeguro();
    $order = Order::factory()->initial()->create();

    $gateway->setEncryptedCard('blob');
    $result = $gateway->createCheckout($order);

    expect($result->embeddedData['status'])->toBe('failed')
        ->and($result->embeddedData['failure_reason'])->toBe('Cartão recusado pela emissora')
        ->and(PaymentMethodToken::count())->toBe(0);
});

test('verifySignature valida o SHA256 de token-payload bruto', function () {
    [$gateway] = makePagSeguro();
    $payload = '{"reference_id":"order-9","charges":[{"id":"CHAR_1","status":"PAID"}]}';

    $valid = Request::create('/webhook', 'POST', server: [
        'HTTP_X_AUTHENTICITY_TOKEN' => hash('sha256', 'sandbox-token-abc-'.$payload),
        'CONTENT_TYPE' => 'application/json',
    ], content: $payload);

    $invalid = Request::create('/webhook', 'POST', server: [
        'HTTP_X_AUTHENTICITY_TOKEN' => hash('sha256', 'token-errado-'.$payload),
        'CONTENT_TYPE' => 'application/json',
    ], content: $payload);

    expect($gateway->verifySignature($valid))->toBeTrue()
        ->and($gateway->verifySignature($invalid))->toBeFalse();
});

test('handleWebhook mapeia status do PagBank pro dominio', function (array $charge, string $expected) {
    [$gateway] = makePagSeguro();

    $request = Request::create('/webhook', 'POST', server: ['CONTENT_TYPE' => 'application/json'],
        content: json_encode(['reference_id' => 'order-9', 'charges' => [$charge]]));

    $result = $gateway->handleWebhook($request);

    expect($result->status)->toBe($expected)
        ->and($result->orderReference)->toBe('order-9');
})->with([
    'pago' => [['id' => 'CHAR_1', 'status' => 'PAID'], 'paid'],
    'recusado' => [['id' => 'CHAR_1', 'status' => 'DECLINED'], 'failed'],
    'reembolsado' => [['id' => 'CHAR_1', 'status' => 'CANCELED', 'amount' => ['summary' => ['refunded' => 4990]]], 'refunded'],
    'chargeback' => [['id' => 'CHAR_1', 'status' => 'CHARGED_BACK'], 'chargeback'],
]);

test('refund chama o cancel da charge e reflete o resultado', function () {
    Http::fake([
        'sandbox.api.pagseguro.com/charges/CHAR_OK/cancel' => Http::response(['status' => 'CANCELED']),
        'sandbox.api.pagseguro.com/charges/CHAR_ERRO/cancel' => Http::response(['error' => 'x'], 400),
    ]);

    [$gateway] = makePagSeguro();

    $paid = Order::factory()->create(['status' => 'paid', 'external_reference' => 'CHAR_OK']);
    $failing = Order::factory()->create(['status' => 'paid', 'external_reference' => 'CHAR_ERRO']);
    $noRef = Order::factory()->create(['status' => 'paid']);

    expect($gateway->refund($paid))->toBeTrue()
        ->and($gateway->refund($failing))->toBeFalse()
        ->and($gateway->refund($noRef))->toBeFalse();
});
