<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `loyalty_redemptions` (task-3, seção 3) não tem como configurar
     * QUAL percentual reward_type='discount_next_renewal' concede —
     * sem essa coluna o catálogo não teria como definir o valor do
     * desconto (mesmo critério já usado antes pra completar lacunas
     * pontuais de schema, ex: payouts.payment_reference).
     */
    public function up(): void
    {
        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->nullable()->after('reward_type');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_redemptions', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
