<?php

use App\Models\User;
use App\Models\Vehicle;

// Ver task-15, seção 2.

test('cadastrar veiculo com placa em formato invalido e rejeitado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('vehicles.store'), ['plate' => 'INVALIDA'])
        ->assertSessionHasErrors('plate');

    expect(Vehicle::count())->toBe(0);
});

test('cadastrar veiculo nos dois formatos validos e aceito', function (string $plate) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('vehicles.store'), ['plate' => $plate])
        ->assertRedirect(route('vehicles.index'));

    expect(Vehicle::where('user_id', $user->id)->exists())->toBeTrue();
})->with([
    'formato antigo' => ['ABC1234'],
    'formato mercosul' => ['ABC1D23'],
]);

test('placa ja cadastrada por outro usuario e rejeitada', function () {
    Vehicle::factory()->create(['plate' => 'ABC1234']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('vehicles.store'), ['plate' => 'ABC1234'])
        ->assertSessionHasErrors('plate');
});

test('mesmo usuario tentando cadastrar a propria placa ja cadastrada recebe mensagem de duplicidade', function () {
    $user = User::factory()->create();
    Vehicle::factory()->create(['user_id' => $user->id, 'plate' => 'ABC1234']);

    $response = $this->actingAs($user)->post(route('vehicles.store'), ['plate' => 'ABC1234']);

    $response->assertSessionHasErrors('plate');
    expect(session('errors')->get('plate')[0])->toContain('já está cadastrada');
});

test('remover veiculo faz soft delete e some da listagem, mas mantem historico', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('vehicles.destroy', $vehicle))
        ->assertRedirect(route('vehicles.index'));

    expect($vehicle->fresh()->active)->toBeFalse()
        ->and($vehicle->fresh()->deleted_at)->not->toBeNull()
        ->and(Vehicle::withTrashed()->whereKey($vehicle->id)->exists())->toBeTrue();

    $response = $this->actingAs($user)->get(route('vehicles.index'));
    $response->assertDontSee($vehicle->plate);
});

test('outro usuario nao pode editar nem remover veiculo que nao e dele', function () {
    $owner = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('vehicles.edit', $vehicle))->assertForbidden();
    $this->actingAs($intruder)->delete(route('vehicles.destroy', $vehicle))->assertForbidden();
});
