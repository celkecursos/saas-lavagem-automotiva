<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton de configuração da monetização do estacionamento —
     * mesmo padrão de outras configurações globais do ecossistema
     * (ver task-3, seção 4.1, e task-10). Registro imutável em id,
     * só updated_at (sem created_at).
     */
    public function up(): void
    {
        Schema::create('parking_billing_settings', function (Blueprint $table) {
            $table->id();
            // % cobrada por carro estacionado quando o lava-rápido NÃO se
            // qualifica pro estacionamento gratuito (ver regra na task-10).
            $table->decimal('fee_percentage', 5, 2)->default(10.00);
            // Usado só no cálculo antifraude (ver task-10).
            $table->integer('max_turns_per_day_per_spot')->default(6);
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_billing_settings');
    }
};
