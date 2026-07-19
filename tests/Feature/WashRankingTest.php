<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\CarWashRating;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\WashRedemption;
use App\Services\Ranking\WashRankingService;
use Illuminate\Support\Carbon;

// Ver task-17, e task-13.

function rankedCarWash(string $name = 'Lava Jato Teste'): CarWash
{
    $carWash = CarWash::factory()->approved()->create(['name' => $name]);
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'clube_lavagem',
    ]);

    return $carWash;
}

function rateCarWash(CarWash $carWash, int $score, ?Carbon $at = null): CarWashRating
{
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($user)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);

    $rating = CarWashRating::create([
        'wash_redemption_id' => $redemption->id,
        'car_wash_id' => $carWash->id,
        'user_id' => $user->id,
        'score' => $score,
    ]);

    if ($at !== null) {
        $rating->forceFill(['created_at' => $at])->save();
    }

    return $rating;
}

test('lava-rapido com menos avaliacoes que o minimo nao entra no ranking', function () {
    $carWash = rankedCarWash();

    for ($i = 0; $i < 4; $i++) {
        rateCarWash($carWash, 100);
    }

    $ranking = (new WashRankingService)->topOfMonth();

    expect($ranking)->toHaveCount(0);
});

test('lava-rapido com o minimo de avaliacoes entra no ranking com a media do mes', function () {
    $carWash = rankedCarWash();

    foreach ([80, 90, 100, 70, 60] as $score) {
        rateCarWash($carWash, $score);
    }

    $ranking = (new WashRankingService)->topOfMonth();

    expect($ranking)->toHaveCount(1)
        ->and((float) $ranking->first()->month_average_score)->toBe(80.0);
});

test('avaliacoes de meses anteriores nao contam pro ranking do mes corrente', function () {
    Carbon::setTestNow('2026-03-15 12:00:00');
    $carWash = rankedCarWash();

    // 5 avaliações no mês passado (não deveriam contar).
    for ($i = 0; $i < 5; $i++) {
        rateCarWash($carWash, 100, Carbon::parse('2026-02-10 12:00:00'));
    }

    $ranking = (new WashRankingService)->topOfMonth();

    expect($ranking)->toHaveCount(0);
});

test('ranking ordena por media desc e limita a 10, so aprovados com clube ativo', function () {
    $melhor = rankedCarWash('Melhor Avaliado');
    $pior = rankedCarWash('Pior Avaliado');

    foreach (range(1, 5) as $i) {
        rateCarWash($melhor, 100);
        rateCarWash($pior, 60);
    }

    // Não aprovado: não deve aparecer mesmo com boas notas.
    $naoAprovado = CarWash::factory()->create(['status' => 'pending', 'name' => 'Nao Aprovado']);
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $naoAprovado->id,
        'product' => 'clube_lavagem',
    ]);
    foreach (range(1, 5) as $i) {
        rateCarWash($naoAprovado, 100);
    }

    $ranking = (new WashRankingService)->topOfMonth();

    expect($ranking->pluck('name')->all())->toBe(['Melhor Avaliado', 'Pior Avaliado']);
});

test('tela /ranking mostra o top 1 com a badge de campeao', function () {
    $carWash = rankedCarWash('Campeão do Mês');
    foreach (range(1, 5) as $i) {
        rateCarWash($carWash, 95);
    }

    $response = $this->get(route('ranking'));

    $response->assertOk()
        ->assertSee('Campeão do Mês')
        ->assertSee('🏆', false);
});

test('home tem link pro ranking completo', function () {
    rankedCarWash();

    $this->get('/')->assertOk()->assertSee(route('ranking'), false);
});
