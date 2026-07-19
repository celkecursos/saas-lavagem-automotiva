<?php

use App\Models\CarWash;
use App\Models\SubscriptionCycle;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WashRedemption;

// Ver task-15, seção 3 — conferência visual do veículo antes de confirmar.

function washPreviewEmployee(CarWash $carWash): User
{
    $employee = User::factory()->create();
    $carWash->users()->attach($employee->id, ['role' => 'employee']);

    return $employee;
}

test('buscar codigo mostra placa e marca/modelo/cor do veiculo antes de confirmar', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = washPreviewEmployee($carWash);
    $cycle = SubscriptionCycle::factory()->create();
    $vehicle = Vehicle::factory()->create(['plate' => 'ABC1234', 'brand' => 'Fiat', 'model' => 'Uno', 'color' => 'Branco']);
    WashRedemption::factory()->create([
        'subscription_cycle_id' => $cycle->id,
        'car_wash_id' => $carWash->id,
        'confirmation_code' => '654321',
        'vehicle_id' => $vehicle->id,
    ]);

    $response = $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.lookup'), ['confirmation_code' => '654321']);

    $response->assertOk()
        ->assertSee('ABC1234')
        ->assertSee('Fiat')
        ->assertSee('Uno')
        ->assertSee('Branco');

    // Buscar não confirma sozinho — quota continua intocada.
    expect($cycle->fresh()->quota_used)->toBe(0);
});

test('buscar codigo invalido mostra erro sem quebrar a tela', function () {
    $carWash = CarWash::factory()->approved()->create();
    $employee = washPreviewEmployee($carWash);

    $this->actingAs($employee)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.washes.confirm.lookup'), ['confirmation_code' => '000000'])
        ->assertOk()
        ->assertSee('Código inválido, já usado ou expirado.');
});
