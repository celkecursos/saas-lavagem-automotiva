<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log bruto de todo webhook recebido — idempotência + auditoria
     * (task-4, seção 1). O unique triplo garante que o reenvio do MESMO
     * webhook pelo provedor (timeout) não duplica nada.
     */
    public function up(): void
    {
        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_gateway_id')
                ->constrained('payment_gateways')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('external_reference');
            // Corpo bruto recebido, pra investigar divergência depois.
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['payment_gateway_id', 'external_reference', 'event_type'],
                'payment_webhook_events_idempotency_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
