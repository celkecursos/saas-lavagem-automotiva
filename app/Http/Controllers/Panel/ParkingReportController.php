<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\ParkingBillingCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Relatório simples de ocupação/faturamento do estacionamento, filtrado
 * por período (task-10, seção 6). Cálculo de ocupação é uma amostragem
 * simples (tempo ocupado / tempo disponível), não streaming.
 */
class ParkingReportController extends Controller
{
    public function index(Request $request): View
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));
        $parkingLot = $carWash->parkingLots()->first();

        $periodStart = $request->filled('inicio')
            ? Carbon::parse($request->input('inicio'))->startOfDay()
            : now()->startOfMonth();
        $periodEnd = $request->filled('fim')
            ? Carbon::parse($request->input('fim'))->endOfDay()
            : now()->endOfDay();

        $totalRevenueCents = 0;
        $vehiclesServed = 0;
        $occupancyRate = 0.0;

        if ($parkingLot !== null) {
            $closedSessions = $parkingLot->sessions()
                ->where('status', 'closed')
                ->whereBetween('exit_at', [$periodStart, $periodEnd])
                ->get();

            $totalRevenueCents = (int) $closedSessions->sum('amount_charged_cents');
            $vehiclesServed = $closedSessions->count();

            $occupiedMinutes = $closedSessions->sum(
                fn ($session) => $session->entry_at->max($periodStart)->diffInMinutes($session->exit_at->min($periodEnd))
            );
            $availableMinutes = $periodStart->diffInMinutes($periodEnd) * max(1, $parkingLot->total_spots);
            $occupancyRate = $availableMinutes > 0 ? min(100, ($occupiedMinutes / $availableMinutes) * 100) : 0.0;
        }

        $latestCharge = ParkingBillingCharge::where('car_wash_id', $carWash->id)
            ->latest('period_start')
            ->first();

        return view('panel.parking.report', [
            'carWash' => $carWash,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'totalRevenueCents' => $totalRevenueCents,
            'vehiclesServed' => $vehiclesServed,
            'occupancyRate' => $occupancyRate,
            'latestCharge' => $latestCharge,
        ]);
    }
}
