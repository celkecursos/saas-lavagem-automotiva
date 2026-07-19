<?php

namespace App\Services\Ranking;

use App\Models\CarWash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ranking "Lava-rápido do mês" (task-17) — janela móvel recalculada ao
 * vivo a cada requisição (diferente de car_washes.satisfaction_score,
 * que é média HISTÓRICA usada no cálculo de repasse da task-9). Zero
 * schema novo, zero cache/tabela de histórico na v1.
 */
class WashRankingService
{
    public function topOfMonth(): Collection
    {
        $minRatings = config('wash-club.min_ratings_for_ranking');

        return CarWash::query()
            ->where('car_washes.status', 'approved')
            ->whereHas('productSubscriptions', fn ($query) => $query
                ->where('product', 'clube_lavagem')
                ->where('status', 'active'))
            ->join('car_wash_ratings', 'car_wash_ratings.car_wash_id', '=', 'car_washes.id')
            ->whereBetween('car_wash_ratings.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('car_washes.id')
            ->havingRaw('COUNT(car_wash_ratings.id) >= ?', [$minRatings])
            ->orderByDesc(DB::raw('AVG(car_wash_ratings.score)'))
            ->limit(10)
            ->select('car_washes.*', DB::raw('AVG(car_wash_ratings.score) as month_average_score'))
            ->get();
    }
}
