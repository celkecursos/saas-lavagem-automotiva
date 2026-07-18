<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Transação, agnóstica de qual gateway processou (task-4, seção 1).
     * Também adiciona a FK de parking_billing_charges.order_id, adiada
     * da task-3 porque orders ainda não existia lá.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Quem está pagando: assinante do clube, ou lava-rápido
            // pagando a cobrança do estacionamento (task-10).
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            // Nullable até resolver qual gateway vai processar.
            $table->foreignId('payment_gateway_id')->nullable()
                ->constrained('payment_gateways')->nullOnDelete();
            // Subscription (mensalidade) ou ParkingBillingCharge (task-10).
            $table->morphs('payable');
            $table->integer('amount_cents');
            $table->string('currency', 3)->default('BRL');
            // null = pagamento avulso; 'initial' = 1º pagamento de uma
            // subscription (tokeniza e salva o cartão); 'subsequent' =
            // renovação automática com o cartão salvo (task-4, seção 5).
            $table->enum('recurring_type', ['initial', 'subsequent'])->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'chargeback', 'canceled'])
                ->default('pending');
            // Id da transação NO gateway.
            $table->string('external_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('parking_billing_charges', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')->on('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('parking_billing_charges', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('orders');
    }
};
