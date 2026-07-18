<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_gateway_id' => null,
            // Morph alvo padrão: mensalidade de assinatura (task-4). O
            // model Subscription nasce na task-7 — aqui só a referência.
            'payable_type' => 'App\\Models\\Subscription',
            'payable_id' => 1,
            'amount_cents' => 4990,
            'currency' => 'BRL',
            'recurring_type' => null,
            'status' => 'pending',
            'external_reference' => null,
            'paid_at' => null,
        ];
    }

    public function initial(): static
    {
        return $this->state(['recurring_type' => 'initial']);
    }

    public function subsequent(): static
    {
        return $this->state(['recurring_type' => 'subsequent']);
    }
}
