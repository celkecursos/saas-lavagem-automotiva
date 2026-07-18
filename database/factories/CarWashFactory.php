<?php

namespace Database\Factories;

use App\Models\CarWash;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CarWash>
 */
class CarWashFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Lava Jato '.fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'document' => (string) fake()->unique()->numerify('##############'),
            'phone' => fake()->numerify('(##) #####-####'),
            'email' => fake()->unique()->safeEmail(),
            'address_line' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => 'PR',
            'zip_code' => fake()->numerify('########'),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved', 'approved_at' => now()]);
    }
}
