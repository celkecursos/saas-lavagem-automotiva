<?php

namespace App\Services\Wash;

use App\Models\CarWashRating;
use App\Models\WashRedemption;

/**
 * Avaliação pós-lavagem (task-8, seção 2, passo 7) — alimenta
 * car_washes.satisfaction_score, que decide o percentual de repasse
 * (task-9).
 */
class CarWashRatingService
{
    /**
     * @throws WashRedemptionValidationException
     */
    public function rate(WashRedemption $redemption, int $userId, int $score, ?string $comment = null): CarWashRating
    {
        if ($redemption->status !== 'completed') {
            throw new WashRedemptionValidationException('Só é possível avaliar uma lavagem já confirmada.');
        }

        // Avaliar a MESMA lavagem duas vezes edita a existente, não
        // cria duplicata (task-13, seção 2.4).
        $rating = CarWashRating::updateOrCreate(
            ['wash_redemption_id' => $redemption->id],
            ['car_wash_id' => $redemption->car_wash_id, 'user_id' => $userId, 'score' => $score, 'comment' => $comment],
        );

        $this->recalculateSatisfactionScore($redemption->car_wash_id);

        return $rating;
    }

    private function recalculateSatisfactionScore(int $carWashId): void
    {
        $average = CarWashRating::where('car_wash_id', $carWashId)->avg('score');

        \App\Models\CarWash::whereKey($carWashId)->update(['satisfaction_score' => $average]);
    }
}
