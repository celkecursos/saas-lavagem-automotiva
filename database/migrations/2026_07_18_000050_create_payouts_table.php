<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lote de repasse financeiro para um lava-rápido (ver task-3,
     * seção 3, e task-9).
     */
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_wash_id')->constrained()->restrictOnDelete();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->integer('total_amount_cents');
            $table->enum('status', ['pending', 'paid', 'failed'])
                ->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
