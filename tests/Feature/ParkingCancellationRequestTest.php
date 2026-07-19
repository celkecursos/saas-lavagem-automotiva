<?php

use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

// Ver task-10, seção 4.1.

function closedParkingSession(): array
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id]);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    $session = ParkingSession::factory()->closed()->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
    ]);

    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return [$carWash, $session, $owner];
}

function parkingCancellationAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin'])->save();
    $user->assignRole('Administrador');

    return $user;
}

test('solicitar cancelamento de sessao fechada cria pending, nada muda na sessao', function () {
    [$carWash, $session, $owner] = closedParkingSession();

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.request-cancellation', $session), ['reason' => 'Fechei o carro errado'])
        ->assertRedirect(route('panel.parking.exit.index'));

    $request = CancellationRequest::sole();
    expect($request->requestable_type)->toBe(\App\Models\ParkingSession::class)
        ->and($request->requestable_id)->toBe($session->id)
        ->and($request->status)->toBe('pending')
        ->and($session->fresh()->status)->toBe('closed');
});

test('nao pode solicitar cancelamento de sessao ainda aberta', function () {
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create(['car_wash_id' => $carWash->id, 'product' => 'estacionamento']);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id]);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    $session = ParkingSession::factory()->create(['parking_lot_id' => $lot->id, 'parking_rate_id' => $rate->id, 'status' => 'open']);
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.request-cancellation', $session), ['reason' => 'x'])
        ->assertStatus(422);

    expect(CancellationRequest::count())->toBe(0);
});

test('admin aprova: sessao vira canceled, sem mexer em nenhum valor de repasse', function () {
    $this->seed(DatabaseSeeder::class);
    [$carWash, $session, $owner] = closedParkingSession();
    $admin = parkingCancellationAdmin();
    $originalAmount = $session->amount_charged_cents;

    $request = CancellationRequest::create([
        'requestable_type' => \App\Models\ParkingSession::class,
        'requestable_id' => $session->id,
        'requested_by_user_id' => $owner->id,
        'reason' => 'Cobrança errada',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->post(route('cancellation-requests.approve', $request));

    expect($session->fresh()->status)->toBe('canceled')
        ->and($session->fresh()->amount_charged_cents)->toBe($originalAmount)
        ->and($request->fresh()->status)->toBe('approved');
});

test('admin rejeita: sessao permanece closed sem mudanca', function () {
    $this->seed(DatabaseSeeder::class);
    [$carWash, $session, $owner] = closedParkingSession();
    $admin = parkingCancellationAdmin();

    $request = CancellationRequest::create([
        'requestable_type' => \App\Models\ParkingSession::class,
        'requestable_id' => $session->id,
        'requested_by_user_id' => $owner->id,
        'reason' => 'x',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)->post(route('cancellation-requests.reject', $request));

    expect($session->fresh()->status)->toBe('closed');
});
