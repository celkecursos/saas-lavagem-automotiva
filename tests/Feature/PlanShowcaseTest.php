<?php

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Subscription;
use App\Models\User;

// Ver task-13, seção 2.3 — vitrine de planos (task-7, seção 2).

test('/planos exibe so plan_features ativas, ordenadas por sort_order', function () {
    $plan = Plan::factory()->create(['name' => 'Plano Turbo']);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Terceira', 'sort_order' => 3, 'active' => true]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Primeira', 'sort_order' => 1, 'active' => true]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Segunda', 'sort_order' => 2, 'active' => true]);
    PlanFeature::factory()->create(['plan_id' => $plan->id, 'label' => 'Inativa Nao Aparece', 'sort_order' => 0, 'active' => false]);

    $response = $this->get('/planos');

    $response->assertOk()
        ->assertSeeInOrder(['Primeira', 'Segunda', 'Terceira'])
        ->assertDontSee('Inativa Nao Aparece');
});

test('plano sem nenhuma feature ativa renderiza sem erro e sem placeholder', function () {
    Plan::factory()->create(['name' => 'Plano Simples']);

    $this->get('/planos')
        ->assertOk()
        ->assertSee('Plano Simples')
        ->assertDontSee('sem vantagens');
});

test('plano inativo nao aparece na vitrine', function () {
    Plan::factory()->create(['name' => 'Plano Visivel', 'active' => true]);
    Plan::factory()->create(['name' => 'Plano Escondido', 'active' => false]);

    $response = $this->get('/planos');

    $response->assertSee('Plano Visivel')->assertDontSee('Plano Escondido');
});

test('badge "seu plano atual" aparece so pro plano com subscription ativa do usuario', function () {
    $user = User::factory()->create();
    $meuPlano = Plan::factory()->create(['name' => 'Meu Plano']);
    $outroPlano = Plan::factory()->create(['name' => 'Outro Plano']);
    Subscription::factory()->for($user)->for($meuPlano, 'plan')->active()->create();

    $response = $this->actingAs($user)->get('/planos');

    $response->assertOk()->assertSee('seu plano atual');
});
