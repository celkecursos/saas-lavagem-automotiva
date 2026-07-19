<?php

namespace Database\Seeders;

use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuários de TESTE/demonstração (task-23, seção 6). ATENÇÃO: senha
 * única e e-mails fixos — só pra ambiente de desenvolvimento/gravação
 * de vídeo, NUNCA rodar em produção com dados reais.
 */
class UserSeeder extends Seeder
{
    private const PASSWORD = '123456A#b';

    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedAdministrador();
        $this->seedCarWashOwner();
        $this->seedSubscriber();
    }

    private function seedSuperAdmin(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'cesar@celke.com.br'],
            ['name' => 'Cesar', 'password' => Hash::make(self::PASSWORD), 'role' => 'admin', 'email_verified_at' => now()],
        );

        $user->syncRoles(['Super Admin']);
    }

    private function seedAdministrador(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'kelly@celke.com.br'],
            ['name' => 'Kelly', 'password' => Hash::make(self::PASSWORD), 'role' => 'admin', 'email_verified_at' => now()],
        );

        $user->syncRoles(['Administrador']);
    }

    /**
     * jessica@celke.com.br: owner de um car_wash de exemplo com o
     * produto 'estacionamento' ativo — acessa o painel do lava-rápido,
     * módulo de estacionamento, já com dados pra demonstração.
     */
    private function seedCarWashOwner(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'jessica@celke.com.br'],
            ['name' => 'Jessica', 'password' => Hash::make(self::PASSWORD), 'role' => 'user', 'email_verified_at' => now()],
        );

        $carWash = CarWash::updateOrCreate(
            ['document' => '11222333000144'],
            [
                'name' => 'Lava Jato da Jessica',
                'slug' => 'lava-jato-da-jessica',
                'phone' => '(41) 3333-0000',
                'email' => 'contato@lavajatodajessica.com.br',
                'address_line' => 'Av. Demonstração, 500',
                'city' => 'Curitiba',
                'state' => 'PR',
                'zip_code' => '80000000',
                'status' => 'approved',
                'approved_at' => now(),
            ],
        );

        $carWash->users()->syncWithoutDetaching([$user->id => ['role' => 'owner']]);

        CarWashProductSubscription::updateOrCreate(
            ['car_wash_id' => $carWash->id, 'product' => 'estacionamento'],
            ['status' => 'active', 'activated_at' => now()],
        );
    }

    /**
     * gabrielly@celke.com.br: assinante com subscription ativa + ciclo
     * de exemplo, já pronta pra usar o clube de lavagem sem precisar
     * assinar na hora de uma demonstração.
     */
    private function seedSubscriber(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'gabrielly@celke.com.br'],
            ['name' => 'Gabrielly', 'password' => Hash::make(self::PASSWORD), 'role' => 'user', 'email_verified_at' => now()],
        );

        $plan = Plan::firstOrCreate(
            ['slug' => 'essencial'],
            [
                'name' => 'Essencial',
                'price_cents' => 4990,
                'wash_quota' => 4,
                'quota_period' => 'monthly',
                'active' => true,
            ],
        );

        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_period_start' => now()->startOfMonth(),
                'current_period_end' => now()->addMonth(),
            ],
        );

        SubscriptionCycle::firstOrCreate(
            ['subscription_id' => $subscription->id],
            [
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->addMonth(),
                'quota_total' => $plan->wash_quota,
                'quota_used' => 0,
            ],
        );
    }
}
