<?php

use App\Models\Order;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscription\SubscriptionActivator;

// Ver task-16, seção 2, passos 2 e 3.

test('cadastrar com referral_code valido grava referred_by e cria reward pending', function () {
    $referrer = User::factory()->create();

    $this->post('/registro', [
        'name' => 'Indicado',
        'email' => 'indicado@exemplo.com',
        'phone' => '(41) 90000-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
        'referral_code' => $referrer->referral_code,
    ]);

    $indicado = User::where('email', 'indicado@exemplo.com')->sole();

    expect($indicado->referred_by_user_id)->toBe($referrer->id);

    $reward = ReferralReward::sole();
    expect($reward->referrer_user_id)->toBe($referrer->id)
        ->and($reward->referred_user_id)->toBe($indicado->id)
        ->and($reward->status)->toBe('pending');
});

test('codigo invalido nao bloqueia o cadastro, so nao cria vinculo', function () {
    $response = $this->post('/registro', [
        'name' => 'Sem Indicacao',
        'email' => 'semindicacao@exemplo.com',
        'phone' => '(41) 90000-0000',
        'password' => 'Senha#Forte1',
        'password_confirmation' => 'Senha#Forte1',
        'referral_code' => 'CODIGOXX',
    ]);

    $response->assertRedirect(route('plans.index'));

    $user = User::where('email', 'semindicacao@exemplo.com')->sole();

    expect($user->referred_by_user_id)->toBeNull()
        ->and(ReferralReward::count())->toBe(0);
});

test('autoindicacao (codigo do proprio e-mail) e rejeitada', function () {
    $user = User::factory()->create();

    // Simula alguém tentando se autoindicar reenviando o próprio código
    // (cenário de borda; normalmente o código já é de outra pessoa).
    $reflection = new ReflectionMethod(\App\Http\Controllers\RegisterSubscriberController::class, 'applyReferralCode');
    $reflection->setAccessible(true);
    $reflection->invoke(new \App\Http\Controllers\RegisterSubscriberController, $user, $user->referral_code);

    expect($user->fresh()->referred_by_user_id)->toBeNull()
        ->and(ReferralReward::count())->toBe(0);
});

test('subscription do indicado virar active pela 1a vez muda a reward pra qualified', function () {
    $referrer = User::factory()->create();
    $referred = User::factory()->create(['referred_by_user_id' => $referrer->id]);
    $reward = ReferralReward::factory()->create([
        'referrer_user_id' => $referrer->id,
        'referred_user_id' => $referred->id,
        'status' => 'pending',
    ]);

    $subscription = Subscription::factory()->for($referred)->create();
    $order = Order::factory()->initial()->for($referred)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
    ]);

    SubscriptionActivator::activateFromInitialOrder($order);

    expect($reward->fresh()->status)->toBe('qualified')
        ->and($reward->fresh()->qualified_at)->not->toBeNull();
});

test('renovacoes seguintes do MESMO indicado nao geram nova reward (unique ja garante)', function () {
    $referrer = User::factory()->create();
    $referred = User::factory()->create(['referred_by_user_id' => $referrer->id]);
    ReferralReward::factory()->create([
        'referrer_user_id' => $referrer->id,
        'referred_user_id' => $referred->id,
        'status' => 'granted',
    ]);

    $subscription = Subscription::factory()->for($referred)->active()->create();
    $order = Order::factory()->initial()->for($referred)->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
    ]);

    // Segunda "ativação" (idempotência: já está active, não deveria
    // sequer rodar de novo).
    SubscriptionActivator::activateFromInitialOrder($order);

    expect(ReferralReward::where('referred_user_id', $referred->id)->count())->toBe(1);
});
