<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Ver task-16, seção 2, passo 1 — geração automática do código.

test('todo user criado ganha um referral_code unico de 8 caracteres', function () {
    $user = User::factory()->create();

    expect($user->referral_code)->toHaveLength(8)
        ->and($user->referral_code)->toBe(strtoupper($user->referral_code));
});

test('dois users nunca colidem no referral_code', function () {
    $users = User::factory()->count(20)->create();

    expect($users->pluck('referral_code')->unique())->toHaveCount(20);
});

test('referral_code informado explicitamente e respeitado, nao sobrescrito', function () {
    $user = User::factory()->create(['referral_code' => 'FIXOCODE']);

    expect($user->referral_code)->toBe('FIXOCODE');
});
