<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\ParkingSession;
use App\Services\Parking\ParkingSessionService;
use App\Services\Parking\ParkingValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Saída de veículo (task-10, seção 4).
 */
class ParkingExitController extends Controller
{
    public function index(Request $request): View
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));
        $parkingLot = $carWash->parkingLots()->first();

        $sessions = $parkingLot?->sessions()
            ->where('status', 'open')
            ->when($request->filled('plate'), fn ($query) => $query->where('plate', 'like', '%'.strtoupper($request->input('plate')).'%'))
            ->latest('entry_at')
            ->get() ?? collect();

        // Fechadas recentemente — pra permitir solicitar cancelamento
        // de fechamento errado (task-10, seção 4.1).
        $recentlyClosed = $parkingLot?->sessions()
            ->where('status', 'closed')
            ->latest('exit_at')
            ->limit(10)
            ->get() ?? collect();

        return view('panel.parking.exit', compact('sessions', 'recentlyClosed'));
    }

    public function store(Request $request, ParkingSession $parkingSession, ParkingSessionService $service): RedirectResponse
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        // Nunca aceita sessão de outro lava-rápido (mesmo cuidado do
        // código de resgate na task-8).
        abort_unless(
            $parkingSession->parkingLot->car_wash_id === $carWash->id,
            403,
        );

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'card', 'pix'])],
        ]);

        try {
            $service->checkOut($parkingSession, $validated['payment_method']);
        } catch (ParkingValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('panel.parking.exit.index')->with('success', 'Saída registrada.');
    }
}
