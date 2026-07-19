<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solicitação de reembolso de um order — só pra reembolso, chargeback
     * não passa por solicitação (task-21, seção 1).
     */
    public function up(): void
    {
        Schema::create('order_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('initiated_by', ['self_service', 'admin']);
            $table->text('reason');
            // Sem 'pending': pedir já é a decisão (self-service e admin
            // auto-aprovam na criação); só falta processar no gateway.
            $table->enum('status', ['approved', 'processed', 'failed_manual'])->default('approved');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refund_requests');
    }
};
