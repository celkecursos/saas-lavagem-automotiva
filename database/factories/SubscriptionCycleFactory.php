<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionCycle>
 */
class SubscriptionCycleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'period_start' => now(),
            'period_end' => now()->addMonth(),
            'quota_total' => 4,
            'quota_used' => 0,
        ];
    }
}
