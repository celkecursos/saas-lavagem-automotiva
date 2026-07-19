<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida os dois formatos de placa brasileira (task-15, seção 2):
 * antigo ABC1234 e Mercosul ABC1D23. Extraída como Rule própria pra
 * ser testada isolada (task-13, seção 2.4).
 */
class BrazilianLicensePlate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('A placa informada é inválida.');

            return;
        }

        $normalized = strtoupper(str_replace([' ', '-'], '', $value));

        $isOldFormat = (bool) preg_match('/^[A-Z]{3}[0-9]{4}$/', $normalized);
        $isMercosulFormat = (bool) preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $normalized);

        if (! $isOldFormat && ! $isMercosulFormat) {
            $fail('A placa informada é inválida. Use o formato antigo (ABC1234) ou Mercosul (ABC1D23).');
        }
    }
}
