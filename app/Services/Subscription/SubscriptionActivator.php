<?php

namespace App\Services\Subscription;

use App\Models\Order;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Notifications\SubscriptionConfirmed;
use App\Services\Loyalty\AchievementChecker;
use App\Services\Referral\ReferralRewardGranter;

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

        $cycle = SubscriptionCycle::create([
            'subscription_id' => $subscription->id,
            'period_start' => now(),
            'period_end' => $periodEnd,
            // Copiado do plano no momento do ciclo (task-3, seção 3).
            'quota_total' => $plan->wash_quota,
            'quota_used' => 0,
        ]);

        // Bônus de indicação em fila pro INDICADOR (não pro indicado que
        // está ativando agora) — task-16, seção 2, passo 4.
        ReferralRewardGranter::grantPendingRewardsFor($cycle);

        $subscription->user->notify(new SubscriptionConfirmed($subscription->fresh()));

        static::qualifyReferral($subscription);

        AchievementChecker::checkMembershipAnniversary($subscription->user);
    }

    /**
     * Quando a subscription do INDICADO vira 'active' pela PRIMEIRA vez
     * (exige pagamento confirmado, não só cadastro — evita conta fake
     * gerando bônus sem virar cliente de verdade), task-16, seção 2,
     * passo 3.
     */
    private static function qualifyReferral(Subscription $subscription): void
    {
        // "Pela primeira vez": se já existe outra subscription ativa
        // anterior deste user, não é a primeira confirmação.
        $isFirstActivation = Subscription::where('user_id', $subscription->user_id)
            ->where('status', 'active')
            ->where('id', '!=', $subscription->id)
            ->doesntExist();

        if (! $isFirstActivation) {
            return;
        }

        ReferralReward::where('referred_user_id', $subscription->user_id)
            ->where('status', 'pending')
            ->update(['status' => 'qualified', 'qualified_at' => now()]);
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
