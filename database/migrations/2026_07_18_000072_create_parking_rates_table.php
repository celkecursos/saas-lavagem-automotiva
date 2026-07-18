<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de tarifas do estacionamento (ver task-3, seção 4).
     */
    public function up(): void
    {
        Schema::create('parking_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('unit', ['hour', 'day', 'fraction']);
            $table->integer('price_cents');
            // Minutos de tolerância sem cobrança, ex: 10.
            $table->integer('tolerance_minutes')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_rates');
    }
};
