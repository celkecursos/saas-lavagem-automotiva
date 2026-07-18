<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de provedores suportados pelo CÓDIGO (task-4, seção 1) —
     * populado por seeder, sem tela de CRUD. Adicionar um provedor novo
     * é: nova linha no seeder + nova classe PaymentGatewayInterface.
     */
    public function up(): void
    {
        Schema::create('payment_gateway_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            // FQCN da classe que implementa o gateway, ex:
            // App\Services\Payment\PagSeguroGateway.
            $table->string('service_class');
            // redirect = página hospedada do provedor; embedded =
            // formulário/SDK JS no nosso checkout, tokenizando no browser.
            $table->enum('checkout_mode', ['redirect', 'embedded']);
            $table->boolean('requires_api_key')->default(true);
            $table->boolean('supports_webhook')->default(true);
            $table->string('default_endpoint')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_types');
    }
};
