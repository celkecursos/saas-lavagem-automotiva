<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1 registro por lava-rápido por período — mesmo raciocínio de
     * "lote" que payouts, na direção contrária: o lava-rápido deve À
     * plataforma (ver task-3, seção 4.1, e task-10).
     */
    public function up(): void
    {
        Schema::create('parking_billing_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_wash_id')->constrained()->restrictOnDelete();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            // Lavagens 'completed' do clube nesse período (snapshot).
            $table->integer('wash_count');
            // Soma de parking_lots.total_spots do car_wash no período.
            $table->integer('total_spots_snapshot');
            // Sessões 'closed' nesse período.
            $table->integer('parking_sessions_count');
            // wash_count >= total_spots_snapshot?
            $table->boolean('is_free');
            // Só quando is_free=false.
            $table->decimal('fee_percentage_applied', 5, 2)->nullable();
            $table->integer('fee_amount_cents')->nullable();
            // FK -> orders adicionada na task-4 (a tabela orders ainda não
            // existe neste ponto do histórico). Só criado quando
            // is_free=false, pra cobrar via PaymentGatewayFactory.
            $table->unsignedBigInteger('order_id')->nullable();
            // Heurística antifraude disparada (ver task-10).
            $table->boolean('flagged_for_review')->default(false);
            $table->enum('status', ['free', 'pending', 'paid', 'failed']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_billing_charges');
    }
};
