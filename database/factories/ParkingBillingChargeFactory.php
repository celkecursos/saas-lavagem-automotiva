<?php

namespace Database\Factories;

use App\Models\CarWash;
use App\Models\ParkingBillingCharge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingBillingCharge>
 */
class ParkingBillingChargeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_wash_id' => CarWash::factory(),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'wash_count' => 0,
            'total_spots_snapshot' => 0,
            'parking_sessions_count' => 0,
            'is_free' => true,
            'flagged_for_review' => false,
            'status' => 'free',
        ];
    }
}
