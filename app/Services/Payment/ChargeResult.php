<?php

namespace App\Services\Payment;

/**
 * Resultado de uma cobrança automática com cartão salvo (task-4,
 * seção 2). failureReason é exibido no e-mail de past_due (task-7).
 */
final readonly class ChargeResult
{
    /**
     * @param  'paid'|'failed'  $status
     */
    public function __construct(
        public string $status,
        public ?string $externalReference = null,
        public ?string $failureReason = null,
    ) {}
}
