<?php

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Services\Payment\ChargeResult;
use App\Services\Payment\CheckoutResult;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\PaymentGatewayNotConfiguredException;
use App\Services\Payment\WebhookResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

// Ver task-13, seção 2.1.

// Gateway de mentira só pra Factory ter o que instanciar nos testes.
class FakeTestGateway implements PaymentGatewayInterface
{
    public function __construct(public PaymentGateway $gateway) {}

    public function createCheckout(\App\Models\Order $order): CheckoutResult
    {
        return new CheckoutResult(mode: 'embedded');
    }

    public function handleWebhook(\Illuminate\Http\Request $request): WebhookResult
    {
        return new WebhookResult('paid', 'ext', 'ord');
    }

    public function verifySignature(\Illuminate\Http\Request $request): bool
    {
        return true;
    }

    public function refund(\App\Models\Order $order): bool
    {
        return true;
    }

    public function chargeSavedMethod(\App\Models\Order $order, \App\Models\PaymentMethodToken $method): ChargeResult
    {
        return new ChargeResult('paid');
    }

    public function supportsSavedCardRecurring(): bool
    {
        return true;
    }
}

test('make() lanca excecao quando zero gateways ativos', function () {
    PaymentGateway::factory()->create(['is_active' => false]);

    PaymentGatewayFactory::make();
})->throws(PaymentGatewayNotConfiguredException::class);

test('dois gateways ativos (corrida): resolve pro de updated_at mais recente sem derrubar', function () {
    Log::spy();

    $type = PaymentGatewayType::factory()->create(['service_class' => FakeTestGateway::class]);

    $older = PaymentGateway::factory()->for($type, 'type')->active()->create();
    $older->timestamps = false;
    $older->forceFill(['updated_at' => now()->subDay()])->save();

    $newer = PaymentGateway::factory()->for($type, 'type')->active()->create();

    $resolved = PaymentGatewayFactory::make();

    expect($resolved)->toBeInstanceOf(FakeTestGateway::class)
        ->and($resolved->gateway->id)->toBe($newer->id);

    Log::shouldHaveReceived('warning')->once();
});

test('credentials sai de fato criptografado no banco', function () {
    $gateway = PaymentGateway::factory()->create([
        'credentials' => ['token' => 'super-secreto-123'],
    ]);

    $raw = DB::table('payment_gateways')->where('id', $gateway->id)->value('credentials');

    expect($raw)->not->toContain('super-secreto-123')
        ->and($gateway->fresh()->credentials['token'])->toBe('super-secreto-123');
});
