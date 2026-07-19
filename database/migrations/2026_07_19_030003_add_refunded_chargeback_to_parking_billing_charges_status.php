<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * PaymentWebhookController espelha order.status na charge
     * (task-10, seção 5, passo 7) — sem isso, um webhook de reembolso/
     * chargeback (task-21) quebraria ao tentar gravar um valor fora do
     * enum original ('free','pending','paid','failed').
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE parking_billing_charges MODIFY status ENUM('free', 'pending', 'paid', 'failed', 'refunded', 'chargeback') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE parking_billing_charges MODIFY status ENUM('free', 'pending', 'paid', 'failed') NOT NULL");
    }
};
