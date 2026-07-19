<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\User;
use App\Services\Parking\ParkingSessionService;
use Illuminate\Support\Carbon;

// Ver task-10, seção 4, e task-13, seção 2.6.

function exitActiveCarWash(): array
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id]);

    return [$carWash, $lot];
}

function exitOwner(CarWash $carWash): User
{
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return $owner;
}

test('fechar sessao debita o valor e grava payment_method', function () {
    // Congela o relógio: entry_at e o now() interno do checkOut()
    // precisam do MESMO instante de referência, senão alguns
    // milissegundos de execução do teste empurram a duração pra cima
    // de 60min e o arredondamento (ceil) cobra uma hora a mais.
    // ($this->travelTo() não bastou aqui — Carbon::setTestNow() direto
    // funciona de forma confiável.)
    Carbon::setTestNow('2026-01-01 12:00:00');

    [$carWash, $lot] = exitActiveCarWash();
    $owner = exitOwner($carWash);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id, 'unit' => 'hour', 'price_cents' => 500, 'tolerance_minutes' => 0]);
    $session = ParkingSession::factory()->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
        'entry_at' => now()->subHour(),
        'status' => 'open',
    ]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.exit.store', $session), ['payment_method' => 'pix'])
        ->assertRedirect(route('panel.parking.exit.index'));

    $session->refresh();
    expect($session->status)->toBe('closed')
        ->and($session->payment_method)->toBe('pix')
        ->and($session->amount_charged_cents)->toBe(500)
        ->and($session->exit_at)->not->toBeNull();
});

test('fechar uma sessao ja fechada e rejeitado', function () {
    [$carWash, $lot] = exitActiveCarWash();
    $owner = exitOwner($carWash);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);
    $session = ParkingSession::factory()->closed()->create(['parking_lot_id' => $lot->id, 'parking_rate_id' => $rate->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.exit.store', $session), ['payment_method' => 'cash'])
        ->assertSessionHas('error');
});

test('sessao de outro lava-rapido nao pode ser fechada', function () {
    [$carWash] = exitActiveCarWash();
    [$otherCarWash, $otherLot] = exitActiveCarWash();
    $owner = exitOwner($carWash);
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $otherLot->id]);
    $session = ParkingSession::factory()->create(['parking_lot_id' => $otherLot->id, 'parking_rate_id' => $rate->id]);

    $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->post(route('panel.parking.exit.store', $session), ['payment_method' => 'cash'])
        ->assertForbidden();
});

// Cálculo direto no service — casos de borda de tolerância/unidade.
test('calculo respeita tolerance_minutes e a unidade da tarifa', function (string $unit, int $toleranceMinutes, int $entryMinutesAgo, int $priceCents, int $expected) {
    // Instante fixo pra entryAt/exitAt não sofrerem jitter de execução
    // entre as duas chamadas (mesma lição do checkOut acima).
    $now = Carbon::create(2026, 1, 1, 12, 0, 0);
    $rate = ParkingRate::factory()->make(['unit' => $unit, 'tolerance_minutes' => $toleranceMinutes, 'price_cents' => $priceCents]);
    $service = new ParkingSessionService;

    $amount = $service->calculateAmount($rate, $now->copy()->subMinutes($entryMinutesAgo), $now);

    expect($amount)->toBe($expected);
})->with([
    'hour: exatamente na tolerancia, nao cobra' => ['hour', 10, 10, 500, 0],
    'hour: 1 minuto acima da tolerancia cobra 1 hora cheia' => ['hour', 10, 11, 500, 500],
    'hour: 61 minutos cobra 2 horas (fracao incompleta arredonda pra cima)' => ['hour', 0, 61, 500, 1000],
    'day: 25 horas cobra 2 dias' => ['day', 0, 25 * 60, 4000, 8000],
    'fraction: exatamente 1 bloco de 15min cobra 1x' => ['fraction', 15, 15, 200, 200],
    'fraction: 16 minutos cobra 2 blocos de 15min' => ['fraction', 15, 16, 200, 400],
]);
