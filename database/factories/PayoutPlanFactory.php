<?php

namespace Database\Factories;

use App\Models\PayoutPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayoutPlan>
 */
class PayoutPlanFactory extends Factory
{
    public function definition(): array
    {
        $category = fake()->randomElement(['Essencial', 'Turbo', 'Master']);
        $level = fake()->numberBetween(1, 2);

        return [
            'category' => $category,
            'level' => $level,
            'label' => "{$category} Nível {$level}",
            'base_price_cents' => fake()->randomElement([2000, 3000, 4000, 5000]),
            'active' => true,
        ];
    }
}
