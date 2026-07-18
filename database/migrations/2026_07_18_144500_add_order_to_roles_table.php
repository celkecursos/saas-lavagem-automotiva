<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ordem de exibição dos papéis no gerenciador de permissões
     * (task-23, replicando o padrão do projeto adm do ecossistema
     * Celke): "Super Admin" é sempre order=1 e não pode ser reordenado.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('guard_name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
