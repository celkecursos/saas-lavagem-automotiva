<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: quem administra qual lava-rápido (ver task-3, seção 1).
     */
    public function up(): void
    {
        Schema::create('car_wash_users', function (Blueprint $table) {
            $table->id();
            // FK para car_washes adicionada na migration que cria a tabela
            // car_washes (commit seguinte da task-3) — a tabela ainda não
            // existe neste ponto do histórico.
            $table->unsignedBigInteger('car_wash_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'employee']);
            $table->timestamps();

            $table->unique(['car_wash_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_users');
    }
};
