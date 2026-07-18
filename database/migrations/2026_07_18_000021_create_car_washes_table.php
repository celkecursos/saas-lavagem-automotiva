<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estabelecimentos parceiros (ver task-3, seção 2). Também adiciona
     * aqui as FKs de car_wash_users/car_wash_invitations criadas no
     * commit anterior (a tabela car_washes ainda não existia lá).
     */
    public function up(): void
    {
        Schema::create('car_washes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // CNPJ/CPF do responsável.
            $table->string('document', 18)->unique();
            $table->string('phone')->nullable();
            $table->string('email');
            $table->string('address_line');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 9);
            // Útil pra busca "lava-rápido mais perto".
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            // Status GERAL do cadastro, independente de qual produto o
            // estabelecimento contrata (ver car_wash_product_subscriptions).
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            // Média das notas de car_wash_ratings (0-100); nullable =
            // ainda sem avaliação. Usada no cálculo do repasse (task-9).
            $table->decimal('satisfaction_score', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('car_wash_users', function (Blueprint $table) {
            $table->foreign('car_wash_id')
                ->references('id')->on('car_washes')->cascadeOnDelete();
        });

        Schema::table('car_wash_invitations', function (Blueprint $table) {
            $table->foreign('car_wash_id')
                ->references('id')->on('car_washes')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('car_wash_users', function (Blueprint $table) {
            $table->dropForeign(['car_wash_id']);
        });

        Schema::table('car_wash_invitations', function (Blueprint $table) {
            $table->dropForeign(['car_wash_id']);
        });

        Schema::dropIfExists('car_washes');
    }
};
