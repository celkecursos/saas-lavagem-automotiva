<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O estacionamento em si — pertence a um car_wash (ver task-3,
     * seção 4; produto independente do clube de lavagem).
     */
    public function up(): void
    {
        Schema::create('parking_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_wash_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('total_spots');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_lots');
    }
};
