<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Planos de assinatura do clube de lavagem (ver task-3, seção 3).
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('price_cents');
            // Quantas lavagens o plano dá por ciclo.
            $table->integer('wash_quota');
            // Só 'monthly' na v1; enum já pensando em 'weekly'/'yearly'.
            $table->enum('quota_period', ['monthly', 'weekly', 'yearly'])
                ->default('monthly');
            // Cota não usada acumula pro próximo ciclo? Decisão em aberto
            // na task-2 — default "não acumula", configurável por plano.
            $table->boolean('rollover_quota')->default(false);
            // Limite de uso no mesmo lava-rápido no mesmo dia (antiabuso);
            // null = sem limite.
            $table->integer('max_redemptions_per_day_per_car_wash')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
