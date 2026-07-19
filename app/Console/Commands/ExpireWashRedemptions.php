<?php

namespace App\Console\Commands;

use App\Models\WashRedemption;
use Illuminate\Console\Command;

/**
 * Expira códigos de resgate vencidos, a cada minuto (task-8, seção 2,
 * passo 5). Nenhuma cota foi debitada ainda nesse status, nada a
 * devolver.
 */
class ExpireWashRedemptions extends Command
{
    protected $signature = 'wash-redemptions:expire';

    protected $description = 'Marca como expirado todo código de resgate requested vencido';

    public function handle(): int
    {
        $expired = WashRedemption::where('status', 'requested')
            ->where('code_expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("{$expired} código(s) expirado(s).");

        return self::SUCCESS;
    }
}
