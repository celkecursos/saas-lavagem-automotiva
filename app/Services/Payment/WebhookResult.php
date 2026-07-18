<?php

namespace App\Services\Payment;

/**
 * Resultado de um webhook processado (task-4, seção 2). orderReference
 * é a NOSSA referência ecoada de volta pelo gateway — sem ela o
 * controller não sabe a qual Order o evento pertence.
 */
final readonly class WebhookResult
{
    /**
     * @param  'paid'|'failed'|'refunded'|'chargeback'  $status
     */
    public function __construct(
        public string $status,
        public string $externalReference,
        public string $orderReference,
        public ?string $eventType = null,
    ) {}
}
