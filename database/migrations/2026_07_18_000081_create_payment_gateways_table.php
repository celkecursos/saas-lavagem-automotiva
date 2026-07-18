<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Instâncias configuradas de gateway — é aqui que o ADM escolhe qual
     * está ativo (task-4, seção 1). Ativar um desativa os demais (regra
     * no controller/transação, não constraint de banco).
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_gateway_type_id')
                ->constrained('payment_gateway_types')->restrictOnDelete();
            // Rótulo livre pro admin diferenciar duas instâncias do mesmo
            // tipo (ex: "PagSeguro - conta principal").
            $table->string('label')->nullable();
            // Cast "encrypted:array" no model — nunca texto puro no banco.
            $table->text('credentials');
            $table->boolean('sandbox_mode')->default(true);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
