<?php

use App\Models\ReferralReward;
use App\Models\User;

// Ver task-16, seção 3 — tela "Minhas indicações".

test('painel mostra o proprio codigo, link pronto e lista de indicacoes com status', function () {
    $referrer = User::factory()->create();
    $indicado1 = User::factory()->create(['name' => 'Amigo Um']);
    $indicado2 = User::factory()->create(['name' => 'Amigo Dois']);
    ReferralReward::factory()->create([
        'referrer_user_id' => $referrer->id,
        'referred_user_id' => $indicado1->id,
        'status' => 'granted',
    ]);
    ReferralReward::factory()->create([
        'referrer_user_id' => $referrer->id,
        'referred_user_id' => $indicado2->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($referrer)->get('/indicacoes');

    $response->assertOk()
        ->assertSee($referrer->referral_code)
        // O link em si é montado por Alpine no browser (x-text) a partir
        // do JSON @js() embutido no x-data — verifica que o código e o
        // path /registro estão presentes na página renderizada.
        ->assertSee('/registro?ref='.$referrer->referral_code, false)
        ->assertSee('Amigo Um')
        ->assertSee('Amigo Dois')
        ->assertSee('1'); // contador de lavagens grátis ganhas
});

test('sem nenhuma indicacao, painel mostra estado vazio', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/indicacoes')
        ->assertOk()
        ->assertSee('Você ainda não indicou ninguém.');
});

test('so mostra as indicacoes do proprio usuario logado, nunca de outro', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $someoneElseReferred = User::factory()->create(['name' => 'Nao Deveria Aparecer']);
    ReferralReward::factory()->create([
        'referrer_user_id' => $other->id,
        'referred_user_id' => $someoneElseReferred->id,
    ]);

    $this->actingAs($me)->get('/indicacoes')
        ->assertOk()
        ->assertDontSee('Nao Deveria Aparecer');
});
