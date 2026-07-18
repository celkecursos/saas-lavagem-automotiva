<?php

use App\Models\CarWash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Ver task-13, seção 2.2 — ativação de produtos (task-5, seção 5).

function ownerOf(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

test('ativar estacionamento e imediato (self-service, sem aprovacao)', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = ownerOf($carWash);

    $this->actingAs($owner)
        ->post(route('panel.products.parking.activate'))
        ->assertRedirect(route('panel.products.index'));

    $subscription = $carWash->productSubscriptions()->sole();

    expect($subscription->product)->toBe('estacionamento')
        ->and($subscription->status)->toBe('active')
        ->and($subscription->activated_at)->not->toBeNull();
});

test('ativar produto com cadastro nao aprovado e bloqueado', function () {
    $carWash = CarWash::factory()->create(['status' => 'pending']);
    $owner = ownerOf($carWash);

    $this->actingAs($owner)
        ->post(route('panel.products.parking.activate'))
        ->assertForbidden();

    expect($carWash->productSubscriptions()->count())->toBe(0);

    // A tela "Meus produtos" nem abre enquanto pendente.
    $this->actingAs($owner)
        ->get(route('panel.products.index'))
        ->assertRedirect(route('panel.dashboard'));
});

test('funcionario nao ativa produto — so o owner', function () {
    $carWash = CarWash::factory()->approved()->create();
    ownerOf($carWash);
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    $this->actingAs($employee)
        ->post(route('panel.products.parking.activate'))
        ->assertForbidden();
});

test('dono pausa o estacionamento por iniciativa propria e audita', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = ownerOf($carWash);

    $this->actingAs($owner)->post(route('panel.products.parking.activate'));
    $this->actingAs($owner)->post(route('panel.products.parking.pause'));

    $subscription = $carWash->productSubscriptions()->sole();

    expect($subscription->status)->toBe('suspended')
        ->and($subscription->suspended_at)->not->toBeNull();

    expect(DB::table('audits')
        ->where('auditable_type', \App\Models\CarWashProductSubscription::class)
        ->where('auditable_id', $subscription->id)
        ->exists())->toBeTrue();
});
