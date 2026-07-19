<?php

namespace App\Services\Loyalty;

use App\Models\LoyaltyPointsLedgerEntry;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyRedemptionClaim;
use App\Models\User;

/**
 * Loja de recompensas (task-20, seção 4) — aplicação síncrona, não
 * fica pendente esperando ninguém.
 */
class LoyaltyRedemptionService
{
    public function redeem(User $user, LoyaltyRedemption $loyaltyRedemption): LoyaltyRedemptionClaim
    {
        if (! $loyaltyRedemption->active) {
            throw new LoyaltyRedemptionValidationException('Essa recompensa não está mais disponível.');
        }

        if (LoyaltyPointsLedgerEntry::balanceFor($user->id) < $loyaltyRedemption->points_cost) {
            throw new LoyaltyRedemptionValidationException('Saldo de pontos insuficiente.');
        }

        if (! $user->subscriptions()->where('status', 'active')->exists()) {
            throw new LoyaltyRedemptionValidationException('Você precisa ter uma assinatura ativa pra resgatar recompensas.');
        }

        $claim = LoyaltyRedemptionClaim::create([
            'user_id' => $user->id,
            'loyalty_redemption_id' => $loyaltyRedemption->id,
            // Congelado: se o admin mudar points_cost depois, não muda
            // retroativamente o que já foi gasto.
            'points_spent' => $loyaltyRedemption->points_cost,
        ]);

        LoyaltyPointsLedgerEntry::create([
            'user_id' => $user->id,
            'points' => -$loyaltyRedemption->points_cost,
            'reason' => 'redemption',
            'reference_type' => LoyaltyRedemptionClaim::class,
            'reference_id' => $claim->id,
            'created_at' => now(),
        ]);

        $this->applyReward($user, $loyaltyRedemption);

        $claim->update(['applied_at' => now()]);

        return $claim->fresh();
    }

    private function applyReward(User $user, LoyaltyRedemption $loyaltyRedemption): void
    {
        if ($loyaltyRedemption->reward_type === 'free_wash') {
            $subscription = $user->subscriptions()->where('status', 'active')->latest('created_at')->first();
            $cycle = $subscription?->cycles()->latest('period_start')->first();

            // Mesmo mecanismo do bônus de indicação (task-16, seção 2,
            // passo 4) — soma direto no ciclo atual, não duplica lógica.
            $cycle?->increment('quota_total');

            return;
        }

        if ($loyaltyRedemption->reward_type === 'discount_next_renewal') {
            $user->subscriptions()->where('status', 'active')->latest('created_at')->first()
                ?->update(['pending_renewal_discount_percent' => $loyaltyRedemption->discount_percent]);
        }
    }
}
