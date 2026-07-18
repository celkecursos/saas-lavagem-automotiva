<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assinatura do usuário no clube de lavagem (ver task-3, seção 3).
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            // 'incomplete' = criada mas aguardando confirmação do primeiro
            // pagamento (não existe período de teste gratuito — ver task-7).
            $table->enum('status', ['incomplete', 'active', 'past_due', 'canceled'])
                ->default('incomplete');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            // Troca de plano agendada pra entrar em vigor só na próxima
            // renovação (ver task-7).
            $table->foreignId('pending_plan_id')->nullable()
                ->constrained('plans')->nullOnDelete();
            // Desconto de fidelidade a aplicar na próxima renovação; zerado
            // depois de aplicado uma vez, não acumula (ver task-20).
            $table->decimal('pending_renewal_discount_percent', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
