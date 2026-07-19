<?php

namespace App\Services\Payout;

use App\Models\CarWash;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\WashRedemption;
use App\Notifications\PayoutGenerated;
use Illuminate\Support\Facades\DB;

/**
 * Cálculo e geração do lote de repasse (task-9, seções 1 e 2). O
 * percentual é resolvido UMA VEZ por car_wash a cada rodada — todas as
 * lavagens do lote usam a nota do lava-rápido no momento da geração,
 * mesmo vindas de dias diferentes dentro do período.
 */
class PayoutGenerationService
{
    public function generate(): void
    {
        WashRedemption::query()
            ->where('status', 'completed')
            ->whereNull('payout_item_id')
            ->select('car_wash_id')
            ->distinct()
            ->pluck('car_wash_id')
            ->each(fn (int $carWashId) => $this->generateForCarWash($carWashId));
    }

    private function generateForCarWash(int $carWashId): void
    {
        $carWash = CarWash::findOrFail($carWashId);

        $redemptions = WashRedemption::where('car_wash_id', $carWashId)
            ->where('status', 'completed')
            ->whereNull('payout_item_id')
            ->get();

        if ($redemptions->isEmpty()) {
            return;
        }

        $percentage = static::percentageFor($carWash->satisfaction_score);

        DB::transaction(function () use ($carWash, $redemptions, $percentage) {
            $payout = Payout::create([
                'car_wash_id' => $carWash->id,
                'period_start' => $redemptions->min('redeemed_at'),
                'period_end' => $redemptions->max('redeemed_at'),
                'status' => 'pending',
                'total_amount_cents' => 0,
            ]);

            $total = 0;

            foreach ($redemptions as $redemption) {
                $amountCents = (int) round($redemption->base_price_cents_snapshot * $percentage);
                $total += $amountCents;

                $item = PayoutItem::create([
                    'payout_id' => $payout->id,
                    'wash_redemption_id' => $redemption->id,
                    'amount_cents' => $amountCents,
                ]);

                $redemption->update(['payout_item_id' => $item->id]);
            }

            $payout->update(['total_amount_cents' => $total]);

            $carWash->users()->wherePivot('role', 'owner')->get()
                ->each(fn ($owner) => $owner->notify(new PayoutGenerated($payout)));
        });
    }

    /**
     * Percentual sobre o valor base conforme a nota de satisfação
     * (task-9, seção 1, item b).
     */
    public static function percentageFor(?float $satisfactionScore): float
    {
        return match (true) {
            $satisfactionScore === null => 0.70,
            $satisfactionScore < 70 => 0.60,
            $satisfactionScore <= 90 => 0.70,
            default => 0.75,
        };
    }
}
