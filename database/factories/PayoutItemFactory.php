<?php

namespace Database\Factories;

use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\WashRedemption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayoutItem>
 */
class PayoutItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payout_id' => Payout::factory(),
            'wash_redemption_id' => WashRedemption::factory(),
            'amount_cents' => fake()->numberBetween(1000, 5000),
        ];
    }
}
