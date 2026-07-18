<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de planos de repasse — gerenciado só pelo admin; o
     * lava-rápido ESCOLHE um da lista, nunca digita valor livre
     * (ver task-3, seção 3, e task-9).
     */
    public function up(): void
    {
        Schema::create('payout_plans', function (Blueprint $table) {
            $table->id();
            // Nome de marketing definido pelo admin (ex: "Essencial",
            // "Turbo") — livre, não é enum fixo no código.
            $table->string('category');
            $table->integer('level');
            $table->string('label');
            // Valor fixo por lavagem confirmada — referência sobre a qual
            // o percentual por nota de satisfação é aplicado (task-9).
            $table->integer('base_price_cents');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_plans');
    }
};
