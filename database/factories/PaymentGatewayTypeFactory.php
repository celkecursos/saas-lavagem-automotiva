<?php

namespace Database\Factories;

use App\Models\PaymentGatewayType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGatewayType>
 */
class PaymentGatewayTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->company(),
            'service_class' => 'App\\Services\\Payment\\PagSeguroGateway',
            'checkout_mode' => 'embedded',
            'requires_api_key' => true,
            'supports_webhook' => true,
            'default_endpoint' => 'https://sandbox.api.pagseguro.com',
        ];
    }
}
