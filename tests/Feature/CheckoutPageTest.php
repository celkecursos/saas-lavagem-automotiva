<?php

use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

// Ver task-13, seção 2.3 — a tela de checkout embedded (task-4, 5.3).
// A chave pública NÃO é credencial cadastrada: é obtida via API
// (GET/POST /public-keys) com o próprio token — aqui, mockada.

test('checkout renderiza formulario, SDK do PagBank e a chave publica obtida via API — nunca o token secreto', function () {
    Http::fake([
        'sandbox.api.pagseguro.com/public-keys/card' => Http::response(['public_key' => 'PUBKEY-VISIVEL-1234']),
    ]);

    $plan = Plan::factory()->create();
    PaymentGateway::factory()->active()->create([
        'credentials' => ['token' => 'TOKEN-SECRETO-DA-API-9999'],
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout");

    $response->assertOk()
        ->assertSee('checkout-sdk-js', false)
        ->assertSee('PUBKEY-VISIVEL-1234')
        ->assertSee('Nome no cartão')
        ->assertDontSee('TOKEN-SECRETO-DA-API-9999');

    // A consulta usa o Bearer token — e-mail não entra em nenhuma chamada.
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer TOKEN-SECRETO-DA-API-9999'));
});

test('quando a chave ainda nao existe no PagBank, cria via POST /public-keys', function () {
    Http::fake([
        'sandbox.api.pagseguro.com/public-keys/card' => Http::response(['message' => 'not found'], 404),
        'sandbox.api.pagseguro.com/public-keys' => Http::response(['public_key' => 'PUBKEY-RECEM-CRIADA'], 201),
    ]);

    $plan = Plan::factory()->create();
    PaymentGateway::factory()->active()->create();

    $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout")
        ->assertOk()
        ->assertSee('PUBKEY-RECEM-CRIADA');
});

test('falha ao obter a chave publica mostra tela amigavel, nao erro 500', function () {
    Http::fake([
        'sandbox.api.pagseguro.com/*' => Http::response(['message' => 'unauthorized'], 401),
    ]);

    $plan = Plan::factory()->create();
    PaymentGateway::factory()->active()->create();

    $this->actingAs(User::factory()->create())
        ->get("/planos/{$plan->slug}/checkout")
        ->assertOk()
        ->assertSee('Pagamento temporariamente indisponível');
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
