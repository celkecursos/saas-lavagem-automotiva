<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\ParkingRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingRate>
 */
class ParkingRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parking_lot_id' => ParkingLot::factory(),
            'name' => 'Hora avulsa',
            'unit' => 'hour',
            'price_cents' => 800,
            'tolerance_minutes' => 10,
            'active' => true,
        ];
    }
}
