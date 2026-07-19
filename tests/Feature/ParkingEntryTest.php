<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\User;

// Ver task-10, seção 3.

function entryActiveCarWash(int $totalSpots = 5): array
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id, 'total_spots' => $totalSpots]);

    return [$carWash, $lot];
}

function entryOwner(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

test('entrada bloqueada quando nao ha vaga livre', function () {
    [$carWash, $lot] = entryActiveCarWash(totalSpots: 1);
    $owner = entryOwner($carWash);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    ParkingSession::factory()->create(['parking_lot_id' => $lot->id, 'parking_rate_id' => $rate->id, 'status' => 'open']);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.entry.store'), ['plate' => 'ABC1234', 'parking_rate_id' => $rate->id])
        ->assertSessionHas('error');

    expect(ParkingSession::where('status', 'open')->count())->toBe(1);
});

test('entrada com 1 tarifa ativa so: nao precisa escolher', function () {
    [$carWash, $lot] = entryActiveCarWash();
    $owner = entryOwner($carWash);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.entry.store'), ['plate' => 'ABC1234'])
        ->assertRedirect(route('panel.parking.sessions.index'));

    $session = ParkingSession::sole();
    expect($session->parking_rate_id)->toBe($rate->id)
        ->and($session->plate)->toBe('ABC1234')
        ->and($session->status)->toBe('open');
});

test('entrada com 2+ tarifas ativas exige escolha explicita', function () {
    [$carWash, $lot] = entryActiveCarWash();
    $owner = entryOwner($carWash);
    ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    $chosen = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.entry.store'), ['plate' => 'ABC1234', 'parking_rate_id' => $chosen->id])
        ->assertRedirect(route('panel.parking.sessions.index'));

    expect(ParkingSession::sole()->parking_rate_id)->toBe($chosen->id);
});

test('entrada com 2+ tarifas sem escolher nenhuma e bloqueada', function () {
    [$carWash, $lot] = entryActiveCarWash();
    $owner = entryOwner($carWash);
    ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.entry.store'), ['plate' => 'ABC1234'])
        ->assertSessionHas('error');

    expect(ParkingSession::count())->toBe(0);
});
