<?php

namespace App\Services;

use App\Models\CancellationRequest;
use App\Models\Payout;
use App\Models\WashRedemption;
use App\Models\User;

/**
 * Aprovação/rejeição de cancellation_requests pelo admin (task-9,
 * seção 3.2). Hoje só WashRedemption é requestable; ParkingSession
 * entra na task-10 reaproveitando a mesma estrutura polimórfica.
 */
class CancellationRequestResolver
{
    public function approve(CancellationRequest $request, User $admin): void
    {
        $request->update([
            'status' => 'approved',
            'resolved_by_user_id' => $admin->id,
            'resolved_at' => now(),
        ]);

        if ($request->requestable_type === WashRedemption::class) {
            $this->approveWashRedemptionCancellation($request->requestable);
        }
    }

    public function reject(CancellationRequest $request, User $admin): void
    {
        // A wash_redemption (ou parking_session) permanece intocada
        // (task-9, seção 3.2).
        $request->update([
            'status' => 'rejected',
            'resolved_by_user_id' => $admin->id,
            'resolved_at' => now(),
        ]);
    }

    private function approveWashRedemptionCancellation(WashRedemption $redemption): void
    {
        $redemption->update(['status' => 'canceled']);

        $this->refundQuotaIfStillCurrentCycle($redemption);
        $this->detachFromPayoutIfStillEditable($redemption);
    }

    /**
     * Se o ciclo daquela lavagem ainda for o CICLO ATUAL da assinatura
     * (ainda não renovou), devolve a cota. Se já fechou/renovou, NÃO
     * mexe no ciclo novo — limitação conhecida da v1 (task-9, §3.2).
     */
    private function refundQuotaIfStillCurrentCycle(WashRedemption $redemption): void
    {
        $cycle = $redemption->subscriptionCycle;
        $isStillCurrentCycle = $cycle->subscription->cycles()
            ->latest('period_start')
            ->first()
            ?->id === $cycle->id;

        if ($isStillCurrentCycle && $cycle->quota_used > 0) {
            $cycle->decrement('quota_used');
        }
    }

    /**
     * - Ainda sem payout_item: nada a fazer, só não entra no próximo lote.
     * - Já num payout 'pending': remove o item e recalcula o total.
     * - Payout já 'paid': NÃO estorna automaticamente — pendência manual
     *   pro admin compensar no lote seguinte (task-9, §3.2).
     */
    private function detachFromPayoutIfStillEditable(WashRedemption $redemption): void
    {
        if ($redemption->payout_item_id === null) {
            return;
        }

        $item = $redemption->payoutItem;
        $payout = $item?->payout;

        if ($payout === null || $payout->status !== 'pending') {
            return;
        }

        $redemption->update(['payout_item_id' => null]);
        $item->delete();

        $payout->update([
            'total_amount_cents' => $payout->items()->sum('amount_cents'),
        ]);
    }
}
