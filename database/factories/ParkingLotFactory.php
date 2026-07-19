<?php

namespace Database\Factories;

use App\Models\CarWash;
use App\Models\ParkingLot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingLot>
 */
class ParkingLotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_wash_id' => CarWash::factory(),
            'name' => 'Pátio Principal',
            'total_spots' => 10,
        ];
    }
}
