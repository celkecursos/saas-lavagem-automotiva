<?php

namespace App\Console\Commands;

use App\Services\Parking\ParkingBillingEvaluationService;
use Illuminate\Console\Command;

/**
 * Gratuidade × cobrança do estacionamento + antifraude (task-10,
 * seção 5). Idempotente: rodar 2x no mesmo período não duplica.
 */
class EvaluateParkingBilling extends Command
{
    protected $signature = 'parking-billing:evaluate {--period=monthly}';

    protected $description = 'Avalia gratuidade x cobrança do estacionamento por car_wash no período anterior';

    public function handle(ParkingBillingEvaluationService $service): int
    {
        $service->evaluate();

        $this->info('Avaliação de cobrança do estacionamento concluída.');

        return self::SUCCESS;
    }
}
