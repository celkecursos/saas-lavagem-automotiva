<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\User;

// Ver task-10, seções 0-2.

function parkingActiveCarWash(): CarWash
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);

    return $carWash;
}

function parkingOwner(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

test('lava-rapido sem estacionamento ativo toma 403', function () {
    $carWash = CarWash::factory()->approved()->create();
    $owner = parkingOwner($carWash);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get(route('panel.parking.sessions.index'))
        ->assertForbidden();
});

test('cadastra o estacionamento (parking_lots) e depois edita', function () {
    $carWash = parkingActiveCarWash();
    $owner = parkingOwner($carWash);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.lot.store'), ['name' => 'Pátio A', 'total_spots' => 20])
        ->assertRedirect(route('panel.parking.sessions.index'));

    $lot = ParkingLot::where('car_wash_id', $carWash->id)->sole();
    expect($lot->name)->toBe('Pátio A')->and($lot->total_spots)->toBe(20);

    // Editar (mesmo endpoint, atualiza em vez de duplicar).
    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.lot.store'), ['name' => 'Pátio A', 'total_spots' => 30]);

    expect(ParkingLot::where('car_wash_id', $carWash->id)->count())->toBe(1)
        ->and($lot->fresh()->total_spots)->toBe(30);
});

test('cadastra tarifa apos ter um estacionamento', function () {
    $carWash = parkingActiveCarWash();
    $owner = parkingOwner($carWash);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.rates.store'), [
            'name' => 'Diária',
            'unit' => 'day',
            'price_cents' => 4000,
            'tolerance_minutes' => 15,
        ])->assertRedirect(route('panel.parking.rates.index'));

    $rate = ParkingRate::where('parking_lot_id', $lot->id)->sole();
    expect($rate->name)->toBe('Diária')->and($rate->active)->toBeTrue();
});

test('nao pode cadastrar tarifa sem ter o estacionamento cadastrado ainda', function () {
    $carWash = parkingActiveCarWash();
    $owner = parkingOwner($carWash);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.rates.store'), [
            'name' => 'Diária',
            'unit' => 'day',
            'price_cents' => 4000,
        ])->assertStatus(422);

    expect(ParkingRate::count())->toBe(0);
});
