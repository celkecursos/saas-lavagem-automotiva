<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\View\View;

/**
 * Histórico de repasses recebidos pelo lava-rápido — conferência/
 * disputa (task-9, seção 4).
 */
class PayoutController extends Controller
{
    public function index(): View
    {
        $payouts = Payout::where('car_wash_id', session('current_car_wash_id'))
            ->latest('created_at')
            ->paginate(15);

        return view('panel.payouts.index', compact('payouts'));
    }

    public function show(Payout $payout): View
    {
        abort_unless($payout->car_wash_id === (int) session('current_car_wash_id'), 403);

        $payout->load('items.washRedemption');

        return view('panel.payouts.show', compact('payout'));
    }
}
