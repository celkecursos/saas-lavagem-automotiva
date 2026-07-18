<?php

use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Ver task-13, seção 2.3 — a tela de checkout embedded (task-4, 5.3).

test('checkout renderiza formulario, SDK do PagBank e a chave publica — nunca o token secreto', function () {
    $plan = Plan::factory()->create();
    PaymentGateway::factory()->active()->create([
        'credentials' => [
            'token' => 'TOKEN-SECRETO-DA-API-9999',
            'public_key' => 'PUBKEY-VISIVEL-1234',
        ],
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout");

    $response->assertOk()
        ->assertSee('checkout-sdk-js', false)
        ->assertSee('PUBKEY-VISIVEL-1234')
        ->assertSee('Nome no cartão')
        ->assertDontSee('TOKEN-SECRETO-DA-API-9999');
});

test('sem gateway ativo o checkout mostra tela amigavel, nao erro 500', function () {
    $plan = Plan::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout")
        ->assertOk()
        ->assertSee('Pagamento temporariamente indisponível');
});

test('plano inativo no checkout retorna 404', function () {
    $plan = Plan::factory()->create(['active' => false]);
    PaymentGateway::factory()->active()->create();

    $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout")
        ->assertNotFound();
});
