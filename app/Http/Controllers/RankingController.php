<?php

namespace App\Http\Controllers;

use App\Services\Ranking\WashRankingService;
use Illuminate\View\View;

/**
 * Ranking público "Lava-rápido do mês" (task-17).
 */
class RankingController extends Controller
{
    public function index(WashRankingService $service): View
    {
        $ranking = $service->topOfMonth();

        return view('ranking', compact('ranking'));
    }
}
