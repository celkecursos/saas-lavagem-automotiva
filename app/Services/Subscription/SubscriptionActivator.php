<?php

namespace App\Services\Subscription;

use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Notifications\SubscriptionConfirmed;

/**
 * Ativa uma subscription a partir do 1º pagamento confirmado 'paid'
 * (task-7, seção 3, passo do webhook). Usado tanto pela confirmação
 * síncrona do gateway embedded (PagBank responde PAID na hora) quanto
 * por um webhook real de gateway assíncrono — mesma lógica, idempotente
 * (reenviar/reprocessar não recria o ciclo nem reativa o que já está
 * ativo).
 */
class SubscriptionActivator
{
    public static function activateFromInitialOrder(Order $order): void
    {
        if ($order->payable_type !== Subscription::class) {
            return;
        }

        /** @var Subscription $subscription */
        $subscription = $order->payable;

        if ($subscription->status === 'active') {
            return;
        }

        $plan = $subscription->plan;
        $periodEnd = static::nextPeriodEnd($plan->quota_period);

        $subscription->update([
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        SubscriptionCycle::create([
            'subscription_id' => $subscription->id,
            'period_start' => now(),
            'period_end' => $periodEnd,
            // Copiado do plano no momento do ciclo (task-3, seção 3).
            'quota_total' => $plan->wash_quota,
            'quota_used' => 0,
        ]);

        $subscription->user->notify(new SubscriptionConfirmed($subscription->fresh()));
    }

    public static function nextPeriodEnd(string $quotaPeriod, ?\Illuminate\Support\Carbon $from = null)
    {
        $from ??= now();

        return match ($quotaPeriod) {
            'weekly' => $from->copy()->addWeek(),
            'yearly' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }
}
