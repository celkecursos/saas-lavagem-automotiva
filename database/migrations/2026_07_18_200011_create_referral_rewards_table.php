<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rastreia cada indicação e o status do bônus (task-16, seção 1).
     * Não é Auditable — mecanismo automático entre usuários, não ação
     * financeira/aprovação de staff.
     */
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            // Cada pessoa indicada só gera 1 linha, mesmo que troque de
            // plano depois.
            $table->foreignId('referred_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'qualified', 'granted'])->default('pending');
            // Quando o amigo confirmou a primeira assinatura paga.
            $table->timestamp('qualified_at')->nullable();
            // Em qual ciclo do INDICADOR o bônus foi efetivamente aplicado.
            $table->foreignId('granted_subscription_cycle_id')->nullable()
                ->constrained('subscription_cycles')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
