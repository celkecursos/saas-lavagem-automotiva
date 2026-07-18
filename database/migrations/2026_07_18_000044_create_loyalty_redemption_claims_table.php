<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada resgate individual de recompensa feito por um assinante
     * (ver task-20).
     */
    public function up(): void
    {
        Schema::create('loyalty_redemption_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_redemption_id')
                ->constrained()->restrictOnDelete();
            // Congelado no momento do resgate — não muda se o admin
            // alterar points_cost depois.
            $table->integer('points_spent');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemption_claims');
    }
};
