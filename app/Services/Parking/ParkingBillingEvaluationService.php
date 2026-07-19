<?php

namespace App\Services\Parking;

use App\Models\CarWash;
use App\Models\ParkingBillingCharge;
use App\Models\ParkingBillingSetting;
use Illuminate\Support\Carbon;

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
            ParkingBillingCharge::create([...$chargeData, 'status' => 'free']);

            return;
        }

        $sessionsRevenue = (int) $this->closedSessionsRevenue($carWash, $periodStart, $periodEnd);
        $feeAmountCents = (int) round($sessionsRevenue * ($settings->fee_percentage / 100));

        ParkingBillingCharge::create([
            ...$chargeData,
            'fee_percentage_applied' => $settings->fee_percentage,
            'fee_amount_cents' => $feeAmountCents,
            'status' => 'pending',
        ]);
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
