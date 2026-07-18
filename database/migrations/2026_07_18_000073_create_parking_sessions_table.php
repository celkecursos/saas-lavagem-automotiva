<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sessão de um veículo no estacionamento: entrada, saída, placa,
     * valor cobrado (ver task-3, seção 4, e task-10).
     */
    public function up(): void
    {
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->restrictOnDelete();
            // Nullable se o estacionamento não controlar vaga a vaga.
            $table->foreignId('parking_spot_id')->nullable()
                ->constrained('parking_spots')->nullOnDelete();
            $table->foreignId('parking_rate_id')
                ->constrained('parking_rates')->restrictOnDelete();
            $table->string('plate', 7);
            $table->timestamp('entry_at');
            // Nullable enquanto o veículo está estacionado.
            $table->timestamp('exit_at')->nullable();
            // Nullable até o fechamento da sessão.
            $table->integer('amount_charged_cents')->nullable();
            // Preenchido só no fechamento (ver task-10).
            $table->enum('payment_method', ['cash', 'card', 'pix'])->nullable();
            $table->enum('status', ['open', 'closed', 'canceled'])
                ->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};
