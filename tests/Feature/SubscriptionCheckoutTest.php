<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use Illuminate\Support\Facades\Http;

// Ver task-13, seção 2.3, e task-7, seção 3.

function subscriberGateway(): PaymentGateway
{
    $type = PaymentGatewayType::factory()->create([
        'slug' => 'pagseguro',
        'service_class' => PagSeguroGateway::class,
    ]);

    return PaymentGateway::factory()->for($type, 'type')->active()->create([
        'credentials' => ['token' => 'sandbox-token-abc'],
    ]);
}

function fakePagSeguroOrder(array $charge): void
{
    Http::fake([
        'sandbox.api.pagseguro.com/orders' => Http::response([
            'id' => 'ORDE_FAKE',
            'charges' => [$charge],
        ], 201),
    ]);
}

test('1o pagamento aprovado cria payment_method_tokens e ativa a assinatura com o 1o ciclo', function () {
    fakePagSeguroOrder([
        'id' => 'CHAR_1',
        'status' => 'PAID',
        'payment_method' => ['card' => ['id' => 'CARD_1', 'brand' => 'visa', 'last_digits' => '2097']],
    ]);
    subscriberGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4, 'quota_period' => 'monthly']);

    $response = $this->actingAs($user)->postJson("/planos/{$plan->slug}/assinar", [
        'encrypted_card' => 'blob-do-browser',
    ]);

    $response->assertOk()->assertJson(['redirect' => route('subscription.show')]);

    $subscription = Subscription::sole();
    $order = Order::sole();

    expect($subscription->status)->toBe('active')
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->current_period_end)->not->toBeNull()
        ->and($order->status)->toBe('paid')
        ->and($order->recurring_type)->toBe('initial');

    $cycle = SubscriptionCycle::where('subscription_id', $subscription->id)->sole();

    expect($cycle->quota_total)->toBe(4)
        ->and($cycle->quota_used)->toBe(0);
});

test('1o pagamento recusado nao ativa nada e devolve o motivo', function () {
    fakePagSeguroOrder([
        'id' => 'CHAR_2',
        'status' => 'DECLINED',
        'payment_response' => ['message' => 'Cartão recusado'],
    ]);
    subscriberGateway();
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $response = $this->actingAs($user)->postJson("/planos/{$plan->slug}/assinar", [
        'encrypted_card' => 'blob',
    ]);

    $response->assertStatus(422)->assertJson(['message' => 'Cartão recusado']);

    expect(Subscription::sole()->status)->toBe('incomplete')
        ->and(Order::sole()->status)->toBe('failed')
        ->and(SubscriptionCycle::count())->toBe(0);
});

test('sem gateway ativo nao cria order nem subscription orfa', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $response = $this->actingAs($user)->postJson("/planos/{$plan->slug}/assinar", [
        'encrypted_card' => 'blob',
    ]);

    $response->assertStatus(503);

    expect(Subscription::count())->toBe(0)
        ->and(Order::count())->toBe(0);
});

test('usuario com assinatura ativa nao consegue assinar outro plano', function () {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->active()->create();
    $plan = Plan::factory()->create();

    $this->actingAs($user)
        ->postJson("/planos/{$plan->slug}/assinar", ['encrypted_card' => 'blob'])
        ->assertStatus(422)
        // O JS do checkout exibe este 'message' na tela; sem ele o usuário
        // via só "Não foi possível processar o pagamento" e ia procurar
        // problema no cartão em vez da assinatura que já tem.
        ->assertJson(['message' => 'Você já tem uma assinatura ativa. Cancele ou troque de plano antes de assinar outro.']);

    expect(Subscription::count())->toBe(1);
});
