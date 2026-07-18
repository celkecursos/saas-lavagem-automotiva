<?php

namespace Database\Factories;

use App\Models\CarWash;
use App\Models\SubscriptionCycle;
use App\Models\WashRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WashRedemption>
 */
class WashRedemptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_cycle_id' => SubscriptionCycle::factory(),
            'car_wash_id' => CarWash::factory(),
            'confirmation_code' => (string) fake()->numberBetween(100000, 999999),
            'code_expires_at' => now()->addMinutes(15),
            'status' => 'requested',
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'redeemed_at' => now(),
        ]);
    }
}
