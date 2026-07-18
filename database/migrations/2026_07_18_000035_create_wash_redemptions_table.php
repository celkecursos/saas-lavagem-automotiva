<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cada lavagem resgatada por um assinante em um lava-rápido — fluxo
     * do código de confirmação detalhado na task-8.
     */
    public function up(): void
    {
        Schema::create('wash_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_cycle_id')
                ->constrained()->cascadeOnDelete();
            $table->foreignId('car_wash_id')->constrained()->restrictOnDelete();
            // Só preenchido quando confirmado.
            $table->timestamp('redeemed_at')->nullable();
            $table->char('confirmation_code', 6);
            $table->timestamp('code_expires_at');
            // Funcionário do lava-rápido que confirmou.
            $table->foreignId('confirmed_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            // Nullable até a geração do código; obrigatório a partir da
            // task-15 (qual veículo do assinante está sendo lavado).
            $table->foreignId('vehicle_id')->nullable()
                ->constrained('vehicles')->nullOnDelete();
            $table->enum('status', ['requested', 'completed', 'expired', 'canceled'])
                ->default('requested');
            // Copiado de payout_plans.base_price_cents quando o status vira
            // 'completed'. NÃO é o valor final do repasse: o percentual pela
            // nota de satisfação só é aplicado ao gerar o payout (task-9).
            $table->integer('base_price_cents_snapshot')->nullable();
            // payout_item_id (FK -> payout_items) é adicionado na migration
            // de repasse financeiro (commit posterior da task-3).
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wash_redemptions');
    }
};
