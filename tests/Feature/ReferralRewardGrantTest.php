<?php

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentMethodToken;
use App\Models\Plan;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Services\Payment\PagSeguroGateway;
use App\Services\Subscription\SubscriptionActivator;
use Illuminate\Support\Facades\Http;

// Ver task-16, seção 2, passo 4 — concessão do bônus na criação de
// subscription_cycles (ativação inicial e renovação).

test('criar um subscription_cycles novo pro indicador com reward qualified soma +1 e marca granted', function () {
    $referrer = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $referrerSubscription = Subscription::factory()->for($referrer)->for($plan, 'plan')->create();
    $reward = ReferralReward::factory()->qualified()->create(['referrer_user_id' => $referrer->id]);

    $order = Order::factory()->initial()->for($referrer)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $referrerSubscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    $cycle = SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->sole();

    expect($cycle->quota_total)->toBe(5) // 4 do plano + 1 do bônus
        ->and($reward->fresh()->status)->toBe('granted')
        ->and($reward->fresh()->granted_subscription_cycle_id)->toBe($cycle->id)
        ->and($reward->fresh()->granted_at)->not->toBeNull();
});

test('multiplas rewards qualified acumuladas somam todas no mesmo ciclo novo', function () {
    $referrer = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $referrerSubscription = Subscription::factory()->for($referrer)->for($plan, 'plan')->create();
    ReferralReward::factory()->qualified()->count(3)->create(['referrer_user_id' => $referrer->id]);

    $order = Order::factory()->initial()->for($referrer)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $referrerSubscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    $cycle = SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->sole();

    expect($cycle->quota_total)->toBe(7); // 4 + 3 bônus
    expect(ReferralReward::where('referrer_user_id', $referrer->id)->where('status', 'granted')->count())->toBe(3);
});

test('reward ja granted nao e considerada de novo em ciclos futuros (idempotencia)', function () {
    // Viagem no tempo determinística (em vez de now()->subDay() real) —
    // evita qualquer corrida de precisão entre a gravação da nova data
    // de vencimento e a comparação <= now() dentro do comando.
    $this->travelTo('2026-01-01 00:00:00');

    $type = PaymentGatewayType::factory()->create(['slug' => 'pagseguro', 'service_class' => PagSeguroGateway::class]);
    $gateway = PaymentGateway::factory()->for($type, 'type')->active()->create(['credentials' => ['token' => 'x']]);
    Http::fake(['*' => Http::response(['id' => 'O', 'charges' => [['id' => 'C', 'status' => 'PAID']]], 201)]);

    $referrer = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $referrerSubscription = Subscription::factory()->for($referrer)->for($plan, 'plan')->active()->create([
        'current_period_end' => now()->subDay(),
    ]);
    PaymentMethodToken::create(['user_id' => $referrer->id, 'payment_gateway_id' => $gateway->id, 'token' => 'CARD_X']);
    ReferralReward::factory()->qualified()->create(['referrer_user_id' => $referrer->id]);

    // 1ª renovação: concede o bônus.
    $this->artisan('subscriptions:renew')->assertSuccessful();
    expect(SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->count())->toBe(1);
    $firstCycle = SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->latest('id')->first();
    expect($firstCycle->quota_total)->toBe(5);

    // 2ª renovação: avança o relógio pra depois do novo current_period_end
    // (não mexe direto na coluna — deixa o próprio ciclo vencer de verdade).
    $this->travelTo(Carbon\Carbon::parse($firstCycle->period_end)->addDay());
    $this->artisan('subscriptions:renew')->assertSuccessful();
    expect(SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->count())->toBe(2);
    $secondCycle = SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->latest('id')->first();

    expect($secondCycle->id)->not->toBe($firstCycle->id)
        ->and($secondCycle->quota_total)->toBe(4);
});

test('sem nenhuma reward qualified, ciclo novo nasce com a cota normal do plano', function () {
    $referrer = User::factory()->create();
    $plan = Plan::factory()->create(['wash_quota' => 4]);
    $referrerSubscription = Subscription::factory()->for($referrer)->for($plan, 'plan')->create();

    $order = Order::factory()->initial()->for($referrer)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $referrerSubscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    expect(SubscriptionCycle::where('subscription_id', $referrerSubscription->id)->sole()->quota_total)->toBe(4);
});
