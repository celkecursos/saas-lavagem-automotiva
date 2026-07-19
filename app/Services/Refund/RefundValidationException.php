<?php

namespace App\Services\Refund;

use RuntimeException;

/**
 * Falha de regra de negócio ao solicitar reembolso (task-21, seção 2)
 * — mensagem amigável pro usuário, não 500.
 */
class RefundValidationException extends RuntimeException {}
