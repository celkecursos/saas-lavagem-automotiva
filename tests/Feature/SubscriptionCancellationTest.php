<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PlanChangeScheduled;
use App\Notifications\SubscriptionCanceled;
use Illuminate\Support\Facades\Notification;

// Ver task-7, seção 5 — cancelamento e troca de plano.

test('cancelar mantem acesso ate o fim do periodo pago, so bloqueia renovacao futura', function () {
    Notification::fake();
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->active()->create();

    $this->actingAs($user)
        ->post(route('subscription.cancel'))
        ->assertRedirect(route('subscription.show'));

    $subscription->refresh();

    expect($subscription->status)->toBe('canceled')
        ->and($subscription->canceled_at)->not->toBeNull()
        // current_period_end não muda — acesso continua até lá.
        ->and($subscription->current_period_end)->not->toBeNull();

    Notification::assertSentTo($user, SubscriptionCanceled::class);
});

test('trocar de plano so agenda pending_plan_id, nao muda o plano atual na hora', function () {
    Notification::fake();
    $user = User::factory()->create();
    $planAtual = Plan::factory()->create();
    $novoPlano = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($planAtual, 'plan')->active()->create();

    $this->actingAs($user)->post(route('subscription.change-plan'), [
        'plan_id' => $novoPlano->id,
    ])->assertRedirect(route('subscription.show'));

    $subscription->refresh();

    expect($subscription->plan_id)->toBe($planAtual->id)
        ->and($subscription->pending_plan_id)->toBe($novoPlano->id)
        ->and($subscription->status)->toBe('active');

    Notification::assertSentTo($user, PlanChangeScheduled::class);
});

test('nao pode trocar pra um plano inativo', function () {
    $user = User::factory()->create();
    $subscription = Subscription::factory()->for($user)->active()->create();
    $inativo = Plan::factory()->create(['active' => false]);

    $this->actingAs($user)->post(route('subscription.change-plan'), [
        'plan_id' => $inativo->id,
    ])->assertStatus(422);

    expect($subscription->fresh()->pending_plan_id)->toBeNull();
});

test('assinatura ja cancelada nao pode ser cancelada de novo', function () {
    $user = User::factory()->create();
    Subscription::factory()->for($user)->create(['status' => 'canceled']);

    $this->actingAs($user)
        ->post(route('subscription.cancel'))
        ->assertNotFound();
});
