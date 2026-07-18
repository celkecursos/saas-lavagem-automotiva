<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Um registro por ciclo de cobrança/uso da assinatura — é aqui que
     * o consumo da cota é controlado (ver task-3, seção 3).
     */
    public function up(): void
    {
        Schema::create('subscription_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            // Copiado do plano no momento do ciclo, pra não quebrar
            // histórico se o plano mudar depois.
            $table->integer('quota_total');
            $table->integer('quota_used')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_cycles');
    }
};
