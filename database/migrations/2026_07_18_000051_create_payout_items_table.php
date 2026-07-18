<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Itens de um lote de repasse (1 item = 1 lavagem confirmada).
     * Também adiciona wash_redemptions.payout_item_id, adiado da
     * migration de wash_redemptions porque payout_items ainda não
     * existia naquele ponto do histórico (ver task-3, seção 3).
     */
    public function up(): void
    {
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wash_redemption_id')
                ->constrained()->restrictOnDelete();
            $table->integer('amount_cents');
            $table->timestamps();
        });

        Schema::table('wash_redemptions', function (Blueprint $table) {
            // Preenchido quando a lavagem entra em algum lote de repasse.
            $table->foreignId('payout_item_id')->nullable()
                ->after('base_price_cents_snapshot')
                ->constrained('payout_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wash_redemptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payout_item_id');
        });

        Schema::dropIfExists('payout_items');
    }
};
