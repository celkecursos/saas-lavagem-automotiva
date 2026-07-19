<?php

namespace App\Console\Commands;

use App\Services\Payout\PayoutGenerationService;
use Illuminate\Console\Command;

/**
 * Gera lotes de repasse a partir das lavagens confirmadas ainda sem
 * payout_item_id (task-9, seção 2). Idempotente: rodar 2x sem novas
 * lavagens não gera payout duplicado nem vazio — o filtro
 * payout_item_id IS NULL já garante isso.
 */
class GeneratePayouts extends Command
{
    protected $signature = 'payouts:generate {--period=monthly}';

    protected $description = 'Gera os lotes de repasse aos lava-rápidos com lavagens confirmadas pendentes';

    public function handle(PayoutGenerationService $service): int
    {
        $service->generate();

        $this->info('Repasses gerados.');

        return self::SUCCESS;
    }
}
