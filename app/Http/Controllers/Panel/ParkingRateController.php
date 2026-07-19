<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Tarifas do estacionamento (task-10, seção 2) — acesso restrito pelo
 * middleware 'parking-active'.
 */
class ParkingRateController extends Controller
{
    public function index(): View
    {
        $parkingLot = $this->currentParkingLot();
        $rates = $parkingLot?->rates()->orderByDesc('active')->get() ?? collect();

        return view('panel.parking.rates', compact('parkingLot', 'rates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $parkingLot = $this->currentParkingLot();

        abort_if($parkingLot === null, 422, 'Cadastre o estacionamento antes de criar tarifas.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::in(['hour', 'day', 'fraction'])],
            'price_cents' => ['required', 'integer', 'min:0'],
            'tolerance_minutes' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
        ]);

        $parkingLot->rates()->create($validated);

        return redirect()->route('panel.parking.rates.index')->with('success', 'Tarifa criada.');
    }

    private function currentParkingLot()
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        return $carWash->parkingLots()->first();
    }
}
