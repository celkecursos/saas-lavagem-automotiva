<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contratação de cada PRODUTO pelo lava-rápido — clube de lavagem e
     * estacionamento são independentes entre si (ver task-3, seção 2).
     * Um lava-rápido pode estar aprovado no cadastro geral mas ter só um
     * dos produtos ativo — esta tabela separa os dois conceitos.
     */
    public function up(): void
    {
        Schema::create('car_wash_product_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_wash_id')->constrained()->cascadeOnDelete();
            $table->enum('product', ['clube_lavagem', 'estacionamento']);
            // 'clube_lavagem' passa por aprovação do admin; 'estacionamento'
            // é 100% self-service e já nasce 'active' (ver task-5, seção 5).
            $table->enum('status', ['pending', 'active', 'suspended', 'canceled'])
                ->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            // Admin que aprovou a ativação — só relevante pra
            // product='clube_lavagem' (ver task-5/task-9).
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            // Plano de repasse escolhido do catálogo do admin — obrigatório
            // pra 'clube_lavagem' poder virar 'active' (ver task-9).
            $table->foreignId('payout_plan_id')->nullable()
                ->constrained('payout_plans')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['car_wash_id', 'product']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_product_subscriptions');
    }
};
