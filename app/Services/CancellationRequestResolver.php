<?php

namespace App\Services;

use App\Models\CancellationRequest;
use App\Models\ParkingSession;
use App\Models\Payout;
use App\Models\User;
use App\Models\WashRedemption;
use App\Notifications\CancellationRequestDecided;
use Illuminate\Support\Facades\Notification;

/**
 * Aprovação/rejeição de cancellation_requests pelo admin (task-9,
 * seção 3.2; ParkingSession reaproveitando a mesma estrutura
 * polimórfica desde a task-10, seção 4.1).
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
        } elseif ($request->requestable_type === ParkingSession::class) {
            $this->approveParkingSessionCancellation($request->requestable);
        }

        $this->notifyOwners($request);
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

        $this->notifyOwners($request);
    }

    private function notifyOwners(CancellationRequest $request): void
    {
        $carWash = $request->requestable_type === WashRedemption::class
            ? $request->requestable->carWash
            : $request->requestable->parkingLot->carWash;

        $owners = $carWash->users()->wherePivot('role', 'owner')->get();

        Notification::send($owners, new CancellationRequestDecided($request));
    }

    private function approveWashRedemptionCancellation(WashRedemption $redemption): void
    {
        $redemption->update(['status' => 'canceled']);

        $this->refundQuotaIfStillCurrentCycle($redemption);
        $this->detachFromPayoutIfStillEditable($redemption);
    }

    /**
     * Sem repasse da plataforma envolvido no estacionamento (o dinheiro
     * vai direto pro lava-rápido) — aprovar só corrige o registro pra
     * fins de relatório/auditoria, não movimenta nenhum valor entre
     * contas (task-10, seção 4.1).
     */
    private function approveParkingSessionCancellation(ParkingSession $session): void
    {
        $session->update(['status' => 'canceled']);
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
