<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Ver task-14, seções 2 e 4 — /admin restrito a users.role='admin'.

function makeAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();

    return $user;
}

test('admin acessa o dashboard e ve os KPIs', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->actingAs(makeAdmin())->get('/admin');

    $response->assertOk()
        ->assertSee('Assinantes ativos')
        ->assertSee('MRR aproximado')
        ->assertSee('Filas com pendência');
});

test('usuario comum toma 403 no painel admin', function () {
    $this->seed(DatabaseSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});
