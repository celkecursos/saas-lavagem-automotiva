<?php

namespace App\Services\Referral;

use App\Models\ReferralReward;
use App\Models\SubscriptionCycle;
use App\Notifications\ReferralRewardGranted;
use App\Services\Loyalty\AchievementChecker;

/**
 * Concede bônus de indicação toda vez que um subscription_cycles novo
 * nasce PRO INDICADOR — ativação inicial ou renovação, mesmo ponto de
 * código (task-16, seção 2, passo 4). O bônus nunca se perde: fica em
 * fila ('qualified') até o indicador ter um ciclo novo pra receber,
 * mesmo se a assinatura dele tiver caído no meio do caminho.
 */
class ReferralRewardGranter
{
    public static function grantPendingRewardsFor(SubscriptionCycle $cycle): void
    {
        $referrerUserId = $cycle->subscription->user_id;

        $rewards = ReferralReward::where('referrer_user_id', $referrerUserId)
            ->where('status', 'qualified')
            ->get();

        if ($rewards->isEmpty()) {
            return;
        }

        // Pode haver mais de uma indicação qualificada acumulada — soma
        // +1 por reward no MESMO ciclo novo.
        $cycle->increment('quota_total', $rewards->count());

        foreach ($rewards as $reward) {
            $reward->update([
                'status' => 'granted',
                'granted_subscription_cycle_id' => $cycle->id,
                'granted_at' => now(),
            ]);
        }

        $referrer = $cycle->subscription->user;
        $referrer->notify(new ReferralRewardGranted($rewards->count()));
        AchievementChecker::checkReferrals($referrer);
    }
}
