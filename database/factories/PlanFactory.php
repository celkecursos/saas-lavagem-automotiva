<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Plano '.fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'price_cents' => fake()->numberBetween(4990, 14990),
            'wash_quota' => fake()->numberBetween(2, 8),
            'quota_period' => 'monthly',
            'rollover_quota' => false,
            'max_redemptions_per_day_per_car_wash' => null,
            'active' => true,
        ];
    }
}
