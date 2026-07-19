<?php

namespace App\Services\Parking;

use App\Models\CarWash;
use App\Models\Order;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingBillingSetting;
use App\Notifications\ParkingBillingChargeFlagged;
use App\Notifications\ParkingBillingChargeGenerated;
use App\Support\AdminRecipients;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Gratuidade × cobrança do estacionamento + antifraude (task-10,
 * seção 5). POR CAR_WASH que tenha o produto 'estacionamento' ativo,
 * cobrindo o período anterior. Idempotente: já existe uma charge pra
 * aquele car_wash+período? Não cria de novo.
 */
class ParkingBillingEvaluationService
{
    public function evaluate(): void
    {
        [$periodStart, $periodEnd] = $this->previousPeriod();

        CarWash::whereHas('productSubscriptions', fn ($query) => $query
            ->where('product', 'estacionamento')
            ->where('status', 'active'))
            ->get()
            ->each(fn (CarWash $carWash) => $this->evaluateCarWash($carWash, $periodStart, $periodEnd));
    }

    private function evaluateCarWash(CarWash $carWash, Carbon $periodStart, Carbon $periodEnd): void
    {
        $alreadyEvaluated = ParkingBillingCharge::where('car_wash_id', $carWash->id)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();

        if ($alreadyEvaluated) {
            return;
        }

        $washCount = $carWash->washRedemptionsCompletedBetween($periodStart, $periodEnd);
        $totalSpotsSnapshot = (int) $carWash->parkingLots()->sum('total_spots');
        $parkingSessionsCount = $this->closedSessionsCount($carWash, $periodStart, $periodEnd);
        $isFree = $washCount >= $totalSpotsSnapshot;

        $settings = ParkingBillingSetting::current();
        $days = $periodStart->diffInDays($periodEnd) + 1;
        $flagged = $parkingSessionsCount > ($totalSpotsSnapshot * $settings->max_turns_per_day_per_spot * $days);

        $chargeData = [
            'car_wash_id' => $carWash->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'wash_count' => $washCount,
            'total_spots_snapshot' => $totalSpotsSnapshot,
            'parking_sessions_count' => $parkingSessionsCount,
            'is_free' => $isFree,
            'flagged_for_review' => $flagged,
        ];

        if ($isFree) {
            $charge = ParkingBillingCharge::create([...$chargeData, 'status' => 'free']);

            $this->notifyIfFlagged($charge);

            return;
        }

        $sessionsRevenue = (int) $this->closedSessionsRevenue($carWash, $periodStart, $periodEnd);
        $feeAmountCents = (int) round($sessionsRevenue * ($settings->fee_percentage / 100));

        $charge = ParkingBillingCharge::create([
            ...$chargeData,
            'fee_percentage_applied' => $settings->fee_percentage,
            'fee_amount_cents' => $feeAmountCents,
            'status' => 'pending',
        ]);

        // Order pendente vinculado à charge — o dono do lava-rápido paga
        // depois pelo MESMO checkout embedded da task-4 (a cobrança não
        // acontece sozinha aqui: não há cartão disponível num comando
        // rodando em background, ver task-10, seção 5, passo 6).
        $owner = $carWash->users()->wherePivot('role', 'owner')->first();

        if ($owner === null) {
            return;
        }

        $order = Order::create([
            'user_id' => $owner->id,
            'payable_type' => ParkingBillingCharge::class,
            'payable_id' => $charge->id,
            'amount_cents' => $feeAmountCents,
            'currency' => 'BRL',
            'recurring_type' => null,
            'status' => 'pending',
        ]);

        $charge->update(['order_id' => $order->id]);

        $owner->notify(new ParkingBillingChargeGenerated($charge));
        $this->notifyIfFlagged($charge);
    }

    private function notifyIfFlagged(ParkingBillingCharge $charge): void
    {
        if (! $charge->flagged_for_review) {
            return;
        }

        Notification::send(
            AdminRecipients::withPermission('parking-billing-charges.index'),
            new ParkingBillingChargeFlagged($charge),
        );
    }

    private function closedSessionsCount(CarWash $carWash, Carbon $periodStart, Carbon $periodEnd): int
    {
        return \App\Models\ParkingSession::whereIn('parking_lot_id', $carWash->parkingLots()->pluck('id'))
            ->where('status', 'closed')
            ->whereBetween('exit_at', [$periodStart, $periodEnd])
            ->count();
    }

    private function closedSessionsRevenue(CarWash $carWash, Carbon $periodStart, Carbon $periodEnd): int
    {
        return \App\Models\ParkingSession::whereIn('parking_lot_id', $carWash->parkingLots()->pluck('id'))
            ->where('status', 'closed')
            ->whereBetween('exit_at', [$periodStart, $periodEnd])
            ->sum('amount_charged_cents');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function previousPeriod(): array
    {
        return [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()];
    }
}
