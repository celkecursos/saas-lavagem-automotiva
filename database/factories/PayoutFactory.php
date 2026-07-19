<?php

namespace Database\Factories;

use App\Models\CarWash;
use App\Models\Payout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payout>
 */
class PayoutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_wash_id' => CarWash::factory(),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'total_amount_cents' => 0,
            'status' => 'pending',
        ];
    }
}
