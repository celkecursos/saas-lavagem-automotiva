<?php

namespace Database\Factories;

use App\Models\CancellationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CancellationRequest>
 */
class CancellationRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requested_by_user_id' => User::factory(),
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
