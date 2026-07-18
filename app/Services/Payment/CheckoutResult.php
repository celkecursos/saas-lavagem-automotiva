<?php

namespace App\Services\Payment;

/**
 * Tudo que a view de checkout precisa pra montar a tela (task-4,
 * seção 2): url de redirect (checkout_mode='redirect') OU dados pro
 * formulário embutido (checkout_mode='embedded').
 */
final readonly class CheckoutResult
{
    /**
     * @param  'redirect'|'embedded'  $mode
     * @param  array<string, mixed>  $embeddedData
     */
    public function __construct(
        public string $mode,
        public ?string $redirectUrl = null,
        public array $embeddedData = [],
        public ?string $externalReference = null,
    ) {}
}
