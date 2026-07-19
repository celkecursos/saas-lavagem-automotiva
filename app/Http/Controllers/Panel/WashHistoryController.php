<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\WashRedemption;
use Illuminate\View\View;

/**
 * Histórico de lavagens confirmadas naquele lava-rápido — conferência
 * antes do repasse (task-8, seção 4; liga com task-9).
 */
class WashHistoryController extends Controller
{
    public function index(): View
    {
        $redemptions = WashRedemption::where('car_wash_id', session('current_car_wash_id'))
            ->with(['vehicle', 'confirmedBy'])
            ->latest('created_at')
            ->paginate(15);

        return view('panel.wash.index', compact('redemptions'));
    }
}
