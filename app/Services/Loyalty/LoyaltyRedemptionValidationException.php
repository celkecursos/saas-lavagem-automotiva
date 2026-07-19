<?php

namespace App\Services\Loyalty;

use RuntimeException;

/**
 * Falha de regra de negócio ao resgatar uma recompensa (task-20,
 * seção 4) — mensagem amigável pro usuário, não 500.
 */
class LoyaltyRedemptionValidationException extends RuntimeException {}
