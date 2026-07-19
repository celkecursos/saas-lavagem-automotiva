<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plate' => Str::upper(fake()->unique()->bothify('???####')),
            'brand' => fake()->randomElement(['Volkswagen', 'Fiat', 'Chevrolet', 'Toyota']),
            'model' => fake()->word(),
            'color' => fake()->safeColorName(),
            'active' => true,
        ];
    }
}
