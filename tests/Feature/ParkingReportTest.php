<?php

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use App\Models\User;
use Illuminate\Support\Carbon;

// Ver task-10, seção 6, e task-13, seção 2.6.

function parkingReportCarWash(): array
{
    $carWash = CarWash::factory()->approved()->create();
    CarWashProductSubscription::factory()->active()->create([
        'car_wash_id' => $carWash->id,
        'product' => 'estacionamento',
    ]);
    $lot = ParkingLot::factory()->create(['car_wash_id' => $carWash->id, 'total_spots' => 10]);
    $owner = User::factory()->create();
    $carWash->users()->attach($owner->id, ['role' => 'owner']);

    return [$carWash, $owner, $lot];
}

test('relatorio soma faturamento e veiculos atendidos so do periodo filtrado', function () {
    Carbon::setTestNow('2026-03-15 12:00:00');
    [$carWash, $owner, $lot] = parkingReportCarWash();
    $rate = ParkingRate::factory()->create(['parking_lot_id' => $lot->id]);

    ParkingSession::factory()->closed()->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
        'entry_at' => Carbon::parse('2026-03-10 10:00:00'),
        'exit_at' => Carbon::parse('2026-03-10 12:00:00'),
        'amount_charged_cents' => 700,
    ]);
    // Fora do período filtrado — não deve entrar na soma.
    ParkingSession::factory()->closed()->create([
        'parking_lot_id' => $lot->id,
        'parking_rate_id' => $rate->id,
        'entry_at' => Carbon::parse('2026-01-01 10:00:00'),
        'exit_at' => Carbon::parse('2026-01-01 12:00:00'),
        'amount_charged_cents' => 999,
    ]);

    $response = $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get(route('panel.parking.report', ['inicio' => '2026-03-01', 'fim' => '2026-03-31']));

    $response->assertOk()->assertSee('7,00')->assertSeeText('1');
});

test('relatorio mostra o status de monetizacao da ultima cobranca gerada', function () {
    [$carWash, $owner] = parkingReportCarWash();
    ParkingBillingCharge::factory()->create([
        'car_wash_id' => $carWash->id,
        'is_free' => false,
        'fee_percentage_applied' => 12.5,
        'period_start' => now()->subMonthNoOverflow()->startOfMonth(),
        'period_end' => now()->subMonthNoOverflow()->endOfMonth(),
    ]);

    $response = $this->actingAs($owner)
        ->withSession(['current_car_wash_id' => $carWash->id])
        ->get(route('panel.parking.report'));

    $response->assertOk()->assertSee('12.50%');
});
