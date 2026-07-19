<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingSession>
 */
class ParkingSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parking_lot_id' => ParkingLot::factory(),
            'parking_rate_id' => ParkingRate::factory(),
            'plate' => strtoupper(fake()->bothify('???####')),
            'entry_at' => now(),
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state([
            'status' => 'closed',
            'exit_at' => now(),
            'amount_charged_cents' => 800,
            'payment_method' => 'cash',
        ]);
    }
}
