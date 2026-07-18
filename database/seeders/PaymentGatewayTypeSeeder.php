<?php

namespace Database\Seeders;

use App\Models\PaymentGatewayType;
use Illuminate\Database\Seeder;

class PaymentGatewayTypeSeeder extends Seeder
{
    /**
     * Catálogo de provedores que o CÓDIGO sabe processar (task-4,
     * seção 4). Adicionar um provedor novo (Mercado Pago, Hotmart) é:
     * nova linha aqui + nova classe PaymentGatewayInterface — sem mexer
     * em payment_gateways nem na Factory.
     */
    public function run(): void
    {
        PaymentGatewayType::updateOrCreate(
            ['slug' => 'pagseguro'],
            [
                'name' => 'PagSeguro / PagBank',
                'service_class' => \App\Services\Payment\PagSeguroGateway::class,
                // 'embedded': o produto Assinaturas/redirect do PagBank não
                // atende a recorrência manual escolhida (task-4, seção 5.1).
                'checkout_mode' => 'embedded',
                'requires_api_key' => true,
                'supports_webhook' => true,
                'default_endpoint' => 'https://sandbox.api.pagseguro.com',
            ],
        );
    }
}
