<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Avaliação do assinante sobre o lava-rápido, feita depois de uma
     * lavagem confirmada — alimenta car_washes.satisfaction_score, que
     * decide o percentual de repasse (ver task-8/task-9).
     */
    public function up(): void
    {
        Schema::create('car_wash_ratings', function (Blueprint $table) {
            $table->id();
            // 1 avaliação por lavagem.
            $table->foreignId('wash_redemption_id')->unique()
                ->constrained()->cascadeOnDelete();
            $table->foreignId('car_wash_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Nota 0-100.
            $table->integer('score');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_ratings');
    }
};
