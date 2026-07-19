<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

/**
 * Os 3 planos da vitrine (task-7). Antes o plano "Essencial" nascia
 * dentro do UserSeeder só pra dar uma assinatura à Gabrielly; agora a
 * vitrine tem os 3 níveis clássicos (entrada / mais vendido / topo) e o
 * UserSeeder apenas reaproveita o Essencial já semeado aqui.
 *
 * updateOrCreate por slug: rodar o seeder de novo corrige preço/cota sem
 * duplicar plano nem quebrar assinaturas que já apontam pro plan_id.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $data) {
            $features = $data['features'];
            unset($data['features']);

            $plan = Plan::updateOrCreate(['slug' => $data['slug']], $data);

            // Recria as vantagens do zero: são puramente descritivas e não
            // têm nada apontando pra elas (ver PlanFeature, não é Auditable).
            $plan->features()->delete();

            foreach ($features as $index => $label) {
                PlanFeature::create([
                    'plan_id' => $plan->id,
                    'label' => $label,
                    'sort_order' => $index,
                    'active' => true,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            [
                'name' => 'Essencial',
                'slug' => 'essencial',
                'price_cents' => 4990,
                'wash_quota' => 4,
                'quota_period' => 'monthly',
                'rollover_quota' => false,
                'max_redemptions_per_day_per_car_wash' => 1,
                'active' => true,
                'features' => [
                    '4 lavagens por mês',
                    'Válido em toda a rede de parceiros',
                    'Agendamento pelo painel do assinante',
                    'Cancele quando quiser',
                ],
            ],
            [
                'name' => 'Completo',
                'slug' => 'completo',
                'price_cents' => 8990,
                'wash_quota' => 8,
                'quota_period' => 'monthly',
                'rollover_quota' => true,
                'max_redemptions_per_day_per_car_wash' => 2,
                'active' => true,
                'features' => [
                    '8 lavagens por mês',
                    'Cota não usada acumula pro mês seguinte',
                    'Até 2 lavagens por dia no mesmo parceiro',
                    'Programa de fidelidade com pontos',
                    'Cancele quando quiser',
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'price_cents' => 14990,
                'wash_quota' => 16,
                'quota_period' => 'monthly',
                'rollover_quota' => true,
                'max_redemptions_per_day_per_car_wash' => 3,
                'active' => true,
                'features' => [
                    '16 lavagens por mês',
                    'Cota não usada acumula pro mês seguinte',
                    'Até 3 lavagens por dia no mesmo parceiro',
                    'Pontos de fidelidade em dobro',
                    'Atendimento prioritário',
                    'Cancele quando quiser',
                ],
            ],
        ];
    }
}
