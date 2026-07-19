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

// Expiração de códigos de resgate vencidos (task-8, seção 2, passo 5).
Schedule::command('wash-redemptions:expire')->everyMinute();

// Geração dos lotes de repasse (task-9, seção 2) — todo dia 1º,
// cobrindo o período anterior.
Schedule::command('payouts:generate')->monthlyOn(1, '02:00');

// Avaliação de cobrança do estacionamento (task-10, seção 5) — todo
// dia 1º, cobrindo o período anterior.
Schedule::command('parking-billing:evaluate')->monthlyOn(1, '03:00');
