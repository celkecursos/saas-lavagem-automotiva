<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Livro-razão de pontos de fidelidade — nunca um campo de saldo
     * único, sempre um lançamento por evento (ver task-3/task-20).
     * Lançamentos são imutáveis: só created_at, sem updated_at.
     */
    public function up(): void
    {
        Schema::create('loyalty_points_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Positivo = ganhou, negativo = gastou.
            $table->integer('points');
            $table->enum('reason', ['achievement', 'redemption', 'admin_adjustment']);
            // Achievement ou LoyaltyRedemption.
            $table->nullableMorphs('reference');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points_ledger');
    }
};
