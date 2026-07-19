<?php

namespace App\Services\Parking;

use RuntimeException;

/**
 * Falha de regra de negócio na entrada/saída de veículo (task-10) —
 * mensagem amigável pro funcionário, não 500.
 */
class ParkingValidationException extends RuntimeException {}
