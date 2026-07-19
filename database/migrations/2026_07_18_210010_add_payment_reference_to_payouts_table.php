<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Marcar como pago" exige referência da transferência (task-9,
     * seção 3) — campo não previsto na modelagem original da task-3.
     */
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('total_amount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
};
