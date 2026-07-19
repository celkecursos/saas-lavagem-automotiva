<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\Order;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use App\Notifications\ParkingBillingChargeGenerated;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

// Ver task-10, seção 5 (passo 6-7), e task-13, seção 2.6.

function payableBillingCarWash(): array
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    ParkingLot::factory()->create(['car_wash_id' => $carWash->id, 'total_spots' => 10]);
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return [$carWash, $owner];
}

function pagSeguroGatewayForBilling(): PaymentGateway
{
    $type = PaymentGatewayType::factory()->create(['slug' => 'pagseguro', 'service_class' => PagSeguroGateway::class]);

    return PaymentGateway::factory()->for($type, 'type')->active()->create(['credentials' => ['token' => 'billing-token']]);
}

test('cobranca nao-gratuita cria order pendente e notifica o dono', function () {
    Notification::fake();
    Carbon::setTestNow('2026-02-15 12:00:00');
    [$carWash, $owner] = payableBillingCarWash();
    $lot = $carWash->parkingLots()->first();
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    ParkingSession::factory()->closed()->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
        'exit_at' => now()->subMonthNoOverflow(),
        'amount_charged_cents' => 1000,
    ]);

    $this->artisan('parking-billing:evaluate');

    $charge = ParkingBillingCharge::sole();
    $order = Order::sole();

    expect($charge->order_id)->toBe($order->id)
        ->and($order->status)->toBe('pending')
        ->and($order->payable_type)->toBe(ParkingBillingCharge::class)
        ->and($order->user_id)->toBe($owner->id);

    Notification::assertSentTo($owner, ParkingBillingChargeGenerated::class);
});

test('tela de checkout da cobranca mostra o valor e a chave publica', function () {
    Http::fake(['sandbox.api.pagseguro.com/public-keys/card' => Http::response(['public_key' => 'PUBKEY-COBRANCA'])]);
    [$carWash, $owner] = payableBillingCarWash();
    $gateway = pagSeguroGatewayForBilling();
    $charge = ParkingBillingCharge::factory()->create([
        'car_wash_id' => $carWash->id,
        'is_free' => false,
        'status' => 'pending',
        'fee_amount_cents' => 500,
    ]);
    Order::create([
        'user_id' => $owner->id,
        'payment_gateway_id' => $gateway->id,
        'payable_type' => ParkingBillingCharge::class,
        'payable_id' => $charge->id,
        'amount_cents' => 500,
        'currency' => 'BRL',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get(route('panel.parking.charges.checkout', $charge));

    $response->assertOk()->assertSee('PUBKEY-COBRANCA')->assertSee('5,00');
});

test('pagar a cobranca com sucesso marca order e charge como paid', function () {
    Http::fake(['sandbox.api.pagseguro.com/orders' => Http::response([
        'id' => 'ORDE', 'charges' => [['id' => 'CHAR_PARK', 'status' => 'PAID']],
    ], 201)]);
    [$carWash, $owner] = payableBillingCarWash();
    $gateway = pagSeguroGatewayForBilling();
    $charge = ParkingBillingCharge::factory()->create([
        'car_wash_id' => $carWash->id,
        'is_free' => false,
        'status' => 'pending',
        'fee_amount_cents' => 500,
    ]);
    $order = Order::create([
        'user_id' => $owner->id,
        'payment_gateway_id' => $gateway->id,
        'payable_type' => ParkingBillingCharge::class,
        'payable_id' => $charge->id,
        'amount_cents' => 500,
        'currency' => 'BRL',
        'status' => 'pending',
    ]);
    $charge->update(['order_id' => $order->id]);

    $response = $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->postJson(route('panel.parking.charges.pay', $charge), ['encrypted_card' => 'blob']);

    $response->assertOk();
    expect($order->fresh()->status)->toBe('paid')
        ->and($charge->fresh()->status)->toBe('paid');
});

test('webhook paid de uma cobranca pending marca a charge como paid', function () {
    [$carWash, $owner] = payableBillingCarWash();
    $gateway = pagSeguroGatewayForBilling();
    $charge = ParkingBillingCharge::factory()->create(['car_wash_id' => $carWash->id, 'status' => 'pending']);
    $order = Order::create([
        'user_id' => $owner->id,
        'payment_gateway_id' => $gateway->id,
        'payable_type' => ParkingBillingCharge::class,
        'payable_id' => $charge->id,
        'amount_cents' => 500,
        'currency' => 'BRL',
        'status' => 'pending',
    ]);
    $charge->update(['order_id' => $order->id]);

    $body = json_encode(['reference_id' => "order-{$order->id}", 'charges' => [['id' => 'CHAR_X', 'status' => 'PAID']]]);
    $signature = hash('sha256', 'billing-token-'.$body);

    $this->call('POST', '/webhooks/pagseguro', [], [], [], [
        'HTTP_X_AUTHENTICITY_TOKEN' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $body)->assertOk();

    expect($order->fresh()->status)->toBe('paid')
        ->and($charge->fresh()->status)->toBe('paid');
});
