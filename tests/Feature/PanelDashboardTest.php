<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// Ver task-14, seção 5, e task-13, seção 2.6.1.

function makeCarWash(array $overrides = []): int
{
    static $sequence = 0;
    $sequence++;

    return DB::table('car_washes')->insertGetId(array_merge([
        'name' => 'Lava Jato '.$sequence,
        'slug' => 'lava-jato-'.$sequence.'-'.uniqid(),
        'document' => sprintf('%04d%010d', $sequence, random_int(0, 9999999999)),
        'email' => 'lava'.$sequence.'@exemplo.com',
        'address_line' => 'Rua 1',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zip_code' => '80000000',
        'status' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

function linkUser(int $carWashId, User $user, string $role = 'owner'): void
{
    DB::table('car_wash_users')->insert([
        'car_wash_id' => $carWashId,
        'user_id' => $user->id,
        'role' => $role,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('usuario sem vinculo com lava-rapido toma 403 no painel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/painel')
        ->assertForbidden();
});

test('cadastro pendente mostra so o banner de status', function () {
    $user = User::factory()->create();
    linkUser(makeCarWash(['status' => 'pending']), $user);

    $this->actingAs($user)->get('/painel')
        ->assertOk()
        ->assertSee('em análise')
        ->assertDontSee('Clube de lavagem')
        ->assertDontSee('Estacionamento');
});

test('cadastro rejeitado mostra o motivo no banner', function () {
    $user = User::factory()->create();
    linkUser(makeCarWash(['status' => 'rejected', 'rejection_reason' => 'Documento inválido']), $user);

    $this->actingAs($user)->get('/painel')
        ->assertOk()
        ->assertSee('rejeitado')
        ->assertSee('Documento inválido');
});

test('aprovado com estacionamento ativo mostra o card do produto', function () {
    $user = User::factory()->create();
    $carWashId = makeCarWash();
    linkUser($carWashId, $user);
    DB::table('car_wash_product_subscriptions')->insert([
        'car_wash_id' => $carWashId,
        'product' => 'estacionamento',
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('parking_lots')->insert([
        'car_wash_id' => $carWashId,
        'name' => 'Pátio',
        'total_spots' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->get('/painel')
        ->assertOk()
        ->assertSee('Estacionamento')
        ->assertSee('Vagas livres agora')
        ->assertDontSee('Clube de lavagem');
});

test('trocar de lava-rapido muda o contexto da sessao e valida acesso', function () {
    $user = User::factory()->create();
    $first = makeCarWash();
    $second = makeCarWash();
    $foreign = makeCarWash();
    linkUser($first, $user);
    linkUser($second, $user, 'employee');

    $this->actingAs($user)->get('/painel');
    expect(session('current_car_wash_id'))->toBe($first);

    $this->actingAs($user)
        ->post('/painel/trocar-lava-rapido', ['car_wash_id' => $second])
        ->assertRedirect(route('panel.dashboard'));
    expect(session('current_car_wash_id'))->toBe($second);

    // car_wash de outra pessoa: bloqueado.
    $this->actingAs($user)
        ->post('/painel/trocar-lava-rapido', ['car_wash_id' => $foreign])
        ->assertForbidden();
});
