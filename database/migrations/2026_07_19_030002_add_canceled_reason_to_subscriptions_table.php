<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distingue cancelamento voluntário (fica null) de revogação
     * imediata por reembolso/chargeback (task-21, seção 2, passo 4).
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('canceled_reason', ['refund', 'chargeback'])->nullable()->after('canceled_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('canceled_reason');
        });
    }
};
