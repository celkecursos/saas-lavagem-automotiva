<?php

namespace Database\Factories;

use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralReward>
 */
class ReferralRewardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'referrer_user_id' => User::factory(),
            'referred_user_id' => User::factory(),
            'status' => 'pending',
        ];
    }

    public function qualified(): static
    {
        return $this->state(['status' => 'qualified', 'qualified_at' => now()]);
    }
}
