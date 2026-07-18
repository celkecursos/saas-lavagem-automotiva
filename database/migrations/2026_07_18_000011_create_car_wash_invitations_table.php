<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convite de equipe do lava-rápido (ver task-3, seção 1; fluxo na task-5).
     */
    public function up(): void
    {
        Schema::create('car_wash_invitations', function (Blueprint $table) {
            $table->id();
            // FK para car_washes adicionada na migration que cria a tabela
            // car_washes (commit seguinte da task-3).
            $table->unsignedBigInteger('car_wash_id');
            $table->string('email');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_wash_invitations');
    }
};
