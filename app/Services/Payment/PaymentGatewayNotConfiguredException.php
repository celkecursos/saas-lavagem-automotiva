<?php

namespace App\Services\Payment;

use RuntimeException;

/**
 * Nenhum gateway ativo em payment_gateways (task-4, seção 3) — o
 * checkout mostra erro amigável em vez de 500 (task-7).
 */
class PaymentGatewayNotConfiguredException extends RuntimeException
{
    public function __construct(string $message = 'Nenhum gateway de pagamento está ativo.')
    {
        parent::__construct($message);
    }
}
