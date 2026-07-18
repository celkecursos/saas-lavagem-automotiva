<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Programa de indicação (task-16, seção 1).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 8)->unique()->nullable()->after('cpf');
            $table->foreignId('referred_by_user_id')->nullable()
                ->after('referral_code')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn('referral_code');
        });
    }
};
