<?php

namespace App\Console\Commands;

use App\Services\Subscription\SubscriptionRenewalService;
use Illuminate\Console\Command;

/**
 * Renovação diária das assinaturas vencidas via cartão salvo (task-7,
 * seção 4). Precisa do scheduler rodando de verdade (schedule:work em
 * dev / supervisor em produção) — o comando existir sozinho não basta.
 */
class RenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew';

    protected $description = 'Cobra a renovação das assinaturas vencidas usando o cartão salvo';

    public function handle(SubscriptionRenewalService $service): int
    {
        $service->renewDueSubscriptions();

        $this->info('Renovação de assinaturas processada.');

        return self::SUCCESS;
    }
}
