<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton — mesmo padrão de parking_billing_settings (task-21,
     * seção 1). Os 7 dias de arrependimento (CDC art. 49) são fixos, não
     * ficam nesta tabela; só a janela ESTENDIDA depois deles é configurável.
     */
    public function up(): void
    {
        Schema::create('refund_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('extended_self_service_enabled')->default(false);
            $table->integer('extended_self_service_until_days')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_settings');
    }
};
