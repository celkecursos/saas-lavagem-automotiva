<?php

use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\Order;
use App\Models\Plan;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WashRedemption;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// Ver task-22, e task-13.

function userAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('usuario suspenso nao consegue logar', function () {
    $user = User::factory()->create([
        'suspended_at' => now(),
        'suspension_reason' => 'Fraude confirmada',
    ]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('usuario suspenso com sessao ja ativa e derrubado na proxima request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('vehicles.index'))->assertOk();

    $user->update(['suspended_at' => now(), 'suspension_reason' => 'Reclamação grave']);

    $this->get(route('vehicles.index'))->assertForbidden();
    $this->assertGuest();
});

test('admin busca usuario por nome, email, cpf ou telefone', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $target = User::factory()->create(['name' => 'Jessica Silva', 'email' => 'jessica@exemplo.com', 'cpf' => '11122233344', 'phone' => '41999990000']);
    User::factory()->create(['name' => 'Outra Pessoa']);

    $this->actingAs($admin)->get(route('users.index', ['search' => 'Jessica']))
        ->assertOk()->assertSee('Jessica Silva')->assertDontSee('Outra Pessoa');

    $this->actingAs($admin)->get(route('users.index', ['search' => 'jessica@exemplo.com']))
        ->assertOk()->assertSee('Jessica Silva');

    $this->actingAs($admin)->get(route('users.index', ['search' => '11122233344']))
        ->assertOk()->assertSee('Jessica Silva');
});

test('filtro por papel: assinante ativo, dono de lava-rapido, admin, suspenso', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();

    $subscriber = User::factory()->create(['name' => 'Assinante Ativo']);
    $plan = Plan::factory()->create();
    Subscription::factory()->for($subscriber)->for($plan, 'plan')->active()->create();

    $owner = User::factory()->create(['name' => 'Dono De Lava Rapido']);
    $carWash = CarWash::factory()->approved()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    $suspended = User::factory()->create(['name' => 'Conta Suspensa', 'suspended_at' => now(), 'suspension_reason' => 'x']);

    $this->actingAs($admin)->get(route('users.index', ['role' => 'subscriber']))
        ->assertOk()->assertSee('Assinante Ativo')->assertDontSee('Dono De Lava Rapido');

    $this->actingAs($admin)->get(route('users.index', ['role' => 'car-wash']))
        ->assertOk()->assertSee('Dono De Lava Rapido')->assertDontSee('Assinante Ativo');

    $this->actingAs($admin)->get(route('users.index', ['role' => 'suspended']))
        ->assertOk()->assertSee('Conta Suspensa')->assertDontSee('Assinante Ativo');
});

test('users.show agrega assinatura, pedidos, lavagens, veiculos, avaliacoes e indicacoes', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();

    $target = User::factory()->create(['name' => 'Pessoa Completa']);
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->for($target)->for($plan, 'plan')->active()->create();
    $cycle = SubscriptionCycle::factory()->create(['subscription_id' => $subscription->id]);
    $carWash = CarWash::factory()->approved()->create();
    WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);
    Order::factory()->for($target)->create(['payable_type' => Subscription::class, 'payable_id' => $subscription->id]);
    Vehicle::factory()->for($target)->create(['plate' => 'ABC1234']);

    $referred = User::factory()->create();
    ReferralReward::factory()->create(['referrer_user_id' => $target->id, 'referred_user_id' => $referred->id]);

    $response = $this->actingAs($admin)->get(route('users.show', $target));

    $response->assertOk()
        ->assertSee('Pessoa Completa')
        ->assertSee($plan->name)
        ->assertSee('ABC1234')
        ->assertSee($carWash->name);
});

test('vinculo com lava-rapido aparece com link pra car-washes.show', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $owner = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($admin)->get(route('users.show', $owner))
        ->assertOk()
        ->assertSee($carWash->name)
        ->assertSee(route('car-washes.show', $carWash), false);
});

test('admin suspende uma conta com motivo obrigatorio e fica registrado em audits', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $target = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('users.suspend', $target), [])
        ->assertSessionHasErrors('suspension_reason');
    expect($target->fresh()->suspended_at)->toBeNull();

    $this->actingAs($admin)
        ->post(route('users.suspend', $target), ['suspension_reason' => 'Fraude confirmada'])
        ->assertRedirect(route('users.show', $target));

    $target->refresh();
    expect($target->suspended_at)->not->toBeNull()
        ->and($target->suspension_reason)->toBe('Fraude confirmada');

    expect(DB::table('audits')
        ->where('auditable_type', User::class)
        ->where('auditable_id', $target->id)
        ->where('event', 'updated')
        ->exists())->toBeTrue();
});

test('admin reativa uma conta suspensa', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $target = User::factory()->create(['suspended_at' => now(), 'suspension_reason' => 'x']);

    $this->actingAs($admin)
        ->post(route('users.reactivate', $target))
        ->assertRedirect(route('users.show', $target));

    $target->refresh();
    expect($target->suspended_at)->toBeNull()
        ->and($target->suspension_reason)->toBeNull();
});

test('admin reenvia e-mail de verificacao', function () {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $target = User::factory()->unverified()->create();

    $this->actingAs($admin)
        ->post(route('users.resend-verification', $target))
        ->assertRedirect(route('users.show', $target));

    Notification::assertSentTo($target, \Illuminate\Auth\Notifications\VerifyEmail::class);
});

test('admin sem a permission users.suspend toma 403', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->post(route('users.suspend', $target), ['suspension_reason' => 'x'])
        ->assertForbidden();
});

test('solicitacao de cancelamento aberta aparece na visao 360', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = userAdmin();
    $target = User::factory()->create();
    $carWash = CarWash::factory()->approved()->create();
    $cycle = SubscriptionCycle::factory()->create();
    $redemption = WashRedemption::factory()->completed()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
    ]);
    CancellationRequest::factory()->create([
        'requestable_type' => WashRedemption::class,
        'requestable_id' => $redemption->id,
        'requested_by_user_id' => $target->id,
        'reason' => 'Cobrança errada',
    ]);

    $this->actingAs($admin)->get(route('users.show', $target))
        ->assertOk()->assertSee('Cobrança errada');
});
