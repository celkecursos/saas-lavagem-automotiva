<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Conquistas da v1 (task-20, seção 2) — code é a chave que o
     * AchievementChecker usa pra saber qual regra checar.
     */
    public function run(): void
    {
        $achievements = [
            ['code' => 'first_wash', 'name' => 'Primeira Lavagem', 'description' => 'Confirmou a primeira lavagem pelo clube.', 'icon' => '🚗', 'points_reward' => 10],
            ['code' => '10_washes', 'name' => 'Cliente Fiel', 'description' => 'Completou 10 lavagens.', 'icon' => '⭐', 'points_reward' => 30],
            ['code' => '50_washes', 'name' => 'Cliente VIP', 'description' => 'Completou 50 lavagens.', 'icon' => '🏆', 'points_reward' => 100],
            ['code' => '1_year_member', 'name' => 'Um Ano de Clube', 'description' => '12 meses de assinatura.', 'icon' => '🎉', 'points_reward' => 50],
            ['code' => '5_ratings', 'name' => 'Crítico', 'description' => 'Avaliou 5 lavagens.', 'icon' => '📝', 'points_reward' => 20],
            ['code' => '3_referrals', 'name' => 'Embaixador', 'description' => 'Indicou 3 amigos com sucesso.', 'icon' => '🤝', 'points_reward' => 40],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['code' => $achievement['code']],
                [...$achievement, 'active' => true],
            );
        }
    }
}
