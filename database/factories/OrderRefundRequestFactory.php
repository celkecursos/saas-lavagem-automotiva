<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderRefundRequest>
 */
class OrderRefundRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'requested_by_user_id' => User::factory(),
            'initiated_by' => 'self_service',
            'reason' => fake()->sentence(),
            'status' => 'approved',
            'requested_at' => now(),
        ];
    }

    public function failedManual(): static
    {
        return $this->state(['status' => 'failed_manual']);
    }

    public function processed(): static
    {
        return $this->state(['status' => 'processed', 'processed_at' => now()]);
    }
}
