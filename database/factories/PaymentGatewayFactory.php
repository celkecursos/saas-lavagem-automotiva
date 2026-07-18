<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_gateway_type_id' => PaymentGatewayType::factory(),
            'label' => fake()->words(2, true),
            'credentials' => [
                'token' => 'sandbox-token-'.fake()->uuid(),
                'public_key' => 'PUB-'.fake()->uuid(),
            ],
            'sandbox_mode' => true,
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }
}
