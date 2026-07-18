<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Fundação de permissões semeada desde a task-3 (seção 6); o
     * UserSeeder com os 4 usuários de demonstração entra na task-23.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            PaymentGatewayTypeSeeder::class,
        ]);
    }
}
