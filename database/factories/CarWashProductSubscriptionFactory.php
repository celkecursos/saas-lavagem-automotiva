<?php

namespace Database\Factories;

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarWashProductSubscription>
 */
class CarWashProductSubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_wash_id' => CarWash::factory(),
            'product' => 'estacionamento',
            'status' => 'pending',
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'activated_at' => now()]);
    }
}
