<?php

namespace App\Services\Wash;

use RuntimeException;

/**
 * Falha de regra de negócio ao solicitar/confirmar um resgate de
 * lavagem (task-8, seção 2) — mensagem amigável pro usuário, não 500.
 */
class WashRedemptionValidationException extends RuntimeException {}
