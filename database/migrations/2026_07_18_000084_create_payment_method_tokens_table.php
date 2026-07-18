<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cartão tokenizado/salvo pra cobrança recorrente automática —
     * genérico entre gateways (task-3, seção 3 / task-4, seção 5). O
     * token só vale pro gateway que o gerou: trocar o gateway ativo
     * quebra a renovação de quem tinha token no antigo (task-7).
     */
    public function up(): void
    {
        Schema::create('payment_method_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_gateway_id')
                ->constrained('payment_gateways')->cascadeOnDelete();
            // Cast "encrypted" no model (ex: card.id devolvido pelo PagBank).
            $table->text('token');
            // Só decorativo, pra exibir "cartão final 1234" no painel.
            $table->string('brand')->nullable();
            $table->string('last_four', 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_tokens');
    }
};
