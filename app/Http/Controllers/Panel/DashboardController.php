<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dashboard do lava-rápido (task-14, seção 5): banner de status quando
 * o cadastro não está aprovado; card-resumo por produto ativo quando
 * está.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $carWash = DB::table('car_washes')
            ->where('id', session('current_car_wash_id'))
            ->first();

        abort_if($carWash === null, 404);

        // Cadastro não aprovado: só o banner de status, nada mais
        // (não há produto ativo pra resumir).
        if ($carWash->status !== 'approved') {
            return view('panel.dashboard', [
                'carWash' => $carWash,
                'activeProducts' => [],
                'summaries' => [],
            ]);
        }

        $activeProducts = DB::table('car_wash_product_subscriptions')
            ->where('car_wash_id', $carWash->id)
            ->where('status', 'active')
            ->pluck('product')
            ->all();

        $summaries = [];

        if (in_array('clube_lavagem', $activeProducts, true)) {
            $summaries['clube_lavagem'] = [
                'washes_this_month' => DB::table('wash_redemptions')
                    ->where('car_wash_id', $carWash->id)
                    ->where('status', 'completed')
                    ->whereBetween('redeemed_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'pending_payout_cents' => (int) DB::table('payouts')
                    ->where('car_wash_id', $carWash->id)
                    ->where('status', 'pending')
                    ->sum('total_amount_cents'),
            ];
        }

        if (in_array('estacionamento', $activeProducts, true)) {
            $totalSpots = (int) DB::table('parking_lots')
                ->where('car_wash_id', $carWash->id)
                ->sum('total_spots');

            $openSessions = DB::table('parking_sessions')
                ->join('parking_lots', 'parking_lots.id', '=', 'parking_sessions.parking_lot_id')
                ->where('parking_lots.car_wash_id', $carWash->id)
                ->where('parking_sessions.status', 'open')
                ->count();

            $latestCharge = DB::table('parking_billing_charges')
                ->where('car_wash_id', $carWash->id)
                ->orderByDesc('period_end')
                ->first();

            $summaries['estacionamento'] = [
                'free_spots' => max(0, $totalSpots - $openSessions),
                'revenue_this_month_cents' => (int) DB::table('parking_sessions')
                    ->join('parking_lots', 'parking_lots.id', '=', 'parking_sessions.parking_lot_id')
                    ->where('parking_lots.car_wash_id', $carWash->id)
                    ->where('parking_sessions.status', 'closed')
                    ->whereBetween('parking_sessions.exit_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('parking_sessions.amount_charged_cents'),
                'latest_charge' => $latestCharge,
            ];
        }

        return view('panel.dashboard', compact('carWash', 'activeProducts', 'summaries'));
    }
}
