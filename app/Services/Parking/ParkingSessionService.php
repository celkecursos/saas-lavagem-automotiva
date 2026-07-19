<?php

namespace App\Services\Parking;

use App\Models\ParkingLot;
use App\Models\ParkingRate;
use App\Models\ParkingSession;
use Illuminate\Support\Carbon;

/**
 * Entrada/saída de veículo no estacionamento (task-10, seções 3 e 4).
 */
class ParkingSessionService
{
    /**
     * @throws ParkingValidationException
     */
    public function checkIn(ParkingLot $parkingLot, string $plate, ?ParkingRate $rate = null): ParkingSession
    {
        $occupied = $parkingLot->sessions()->where('status', 'open')->count();

        if ($occupied >= $parkingLot->total_spots) {
            throw new ParkingValidationException('Estacionamento lotado.');
        }

        $rate ??= $this->resolveDefaultRate($parkingLot);

        if ($rate === null) {
            throw new ParkingValidationException('Escolha uma tarifa.');
        }

        return ParkingSession::create([
            'parking_lot_id' => $parkingLot->id,
            'parking_rate_id' => $rate->id,
            'plate' => strtoupper($plate),
            'entry_at' => now(),
            'status' => 'open',
        ]);
    }

    /**
     * @throws ParkingValidationException
     */
    public function checkOut(ParkingSession $session, string $paymentMethod): ParkingSession
    {
        if ($session->status !== 'open') {
            throw new ParkingValidationException('Sessão já fechada.');
        }

        $exitAt = now();
        $amountCents = $this->calculateAmount($session->parkingRate, $session->entry_at, $exitAt);

        $session->update([
            'status' => 'closed',
            'exit_at' => $exitAt,
            'amount_charged_cents' => $amountCents,
            'payment_method' => $paymentMethod,
        ]);

        return $session;
    }

    /**
     * Cálculo do valor cobrado (task-10, seção 4). tolerance_minutes
     * tem papel duplo por não haver campo próprio de "tamanho da
     * fração" no schema (task-10, seção 2): em 'hour'/'day' é a
     * carência antes de começar a cobrar; em 'fraction' é o próprio
     * tamanho do bloco cobrado (ex: 15 = cobra a cada 15 min).
     *
     *   'hour'     -> max(0, duração - carência), arredonda pra cima em
     *                horas cheias × price_cents
     *   'day'      -> mesma ideia, em dias cheios × price_cents
     *   'fraction' -> arredonda a duração cheia pra cima em blocos de
     *                tolerance_minutes × price_cents (sem carência)
     */
    public function calculateAmount(ParkingRate $rate, Carbon $entryAt, Carbon $exitAt): int
    {
        $totalMinutes = $entryAt->diffInMinutes($exitAt);

        if ($rate->unit === 'fraction') {
            if ($totalMinutes === 0) {
                return 0;
            }

            return (int) ceil($totalMinutes / max(1, $rate->tolerance_minutes)) * $rate->price_cents;
        }

        $chargeableMinutes = max(0, $totalMinutes - $rate->tolerance_minutes);

        if ($chargeableMinutes === 0) {
            return 0;
        }

        return match ($rate->unit) {
            'hour' => (int) ceil($chargeableMinutes / 60) * $rate->price_cents,
            'day' => (int) ceil($chargeableMinutes / 1440) * $rate->price_cents,
        };
    }

    /**
     * Se só houver 1 tarifa ativa, pula a escolha (task-10, seção 3).
     */
    private function resolveDefaultRate(ParkingLot $parkingLot): ?ParkingRate
    {
        $activeRates = $parkingLot->rates()->where('active', true)->get();

        return $activeRates->count() === 1 ? $activeRates->first() : null;
    }
}
