<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\ParkingRate;
use App\Services\Parking\ParkingSessionService;
use App\Services\Parking\ParkingValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Entrada de veículo (task-10, seção 3).
 */
class ParkingEntryController extends Controller
{
    public function create(): View
    {
        $parkingLot = $this->currentParkingLot();
        $rates = $parkingLot?->rates()->where('active', true)->get() ?? collect();

        return view('panel.parking.entry', compact('parkingLot', 'rates'));
    }

    public function store(Request $request, ParkingSessionService $service): RedirectResponse
    {
        $parkingLot = $this->currentParkingLot();

        abort_if($parkingLot === null, 422, 'Cadastre o estacionamento antes de operar entradas.');

        $validated = $request->validate([
            'plate' => ['required', 'string', 'max:8'],
            'parking_rate_id' => ['nullable', 'integer'],
        ]);

        $rate = $request->filled('parking_rate_id')
            ? ParkingRate::where('parking_lot_id', $parkingLot->id)->find($validated['parking_rate_id'])
            : null;

        try {
            $service->checkIn($parkingLot, $validated['plate'], $rate);
        } catch (ParkingValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('panel.parking.sessions.index')->with('success', 'Entrada registrada.');
    }

    private function currentParkingLot()
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        return $carWash->parkingLots()->first();
    }
}
