<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vagas individuais — só necessário se o controle for vaga a vaga
     * (opcional/adiável pra v2, ver task-3, seção 4; a tabela já nasce
     * aqui pra manter o schema completo da task-3 num lugar só).
     */
    public function up(): void
    {
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_lot_id')->constrained()->cascadeOnDelete();
            // Identificação da vaga, ex: "A12".
            $table->string('code');
            $table->enum('status', ['available', 'occupied', 'maintenance'])
                ->default('available');
            $table->timestamps();

            $table->unique(['parking_lot_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
