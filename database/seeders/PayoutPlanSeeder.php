<?php

namespace Database\Seeders;

use App\Models\PayoutPlan;
use Illuminate\Database\Seeder;

class PayoutPlanSeeder extends Seeder
{
    /**
     * Catálogo de planos de repasse — exemplos da task-3, seção 3
     * (dados de seed, não fixos no schema).
     */
    public function run(): void
    {
        $plans = [
            ['category' => 'Essencial', 'level' => 1, 'base_price_cents' => 2000],
            ['category' => 'Essencial', 'level' => 2, 'base_price_cents' => 3000],
            ['category' => 'Turbo', 'level' => 1, 'base_price_cents' => 4000],
            ['category' => 'Turbo', 'level' => 2, 'base_price_cents' => 5000],
        ];

        foreach ($plans as $plan) {
            PayoutPlan::updateOrCreate(
                ['category' => $plan['category'], 'level' => $plan['level']],
                [
                    'label' => "{$plan['category']} Nível {$plan['level']}",
                    'base_price_cents' => $plan['base_price_cents'],
                    'active' => true,
                ],
            );
        }
    }
}
