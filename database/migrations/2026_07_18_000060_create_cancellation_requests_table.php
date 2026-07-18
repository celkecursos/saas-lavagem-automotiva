<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solicitação de cancelamento de uma lavagem JÁ confirmada (ou de
     * uma parking_session fechada, ver task-10) — quem confirmou errado
     * não desfaz sozinho; pede e o admin decide (ver task-3, seção 3).
     */
    public function up(): void
    {
        Schema::create('cancellation_requests', function (Blueprint $table) {
            $table->id();
            // WashRedemption ou ParkingSession.
            $table->morphs('requestable');
            // O assinante ou o funcionário que percebeu o erro.
            $table->foreignId('requested_by_user_id')
                ->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            // Admin que decidiu.
            $table->foreignId('resolved_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
