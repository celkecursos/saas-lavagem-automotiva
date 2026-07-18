<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Renovação de assinaturas vencidas (task-7, seção 4) — precisa do
// scheduler rodando de verdade (schedule:work em dev, supervisor/cron
// em produção), não basta o comando existir.
Schedule::command('subscriptions:renew')->daily();
