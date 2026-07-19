<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Meu estacionamento" — dashboard com vagas livres/ocupadas, e
 * criação/edição do parking_lot (task-10, seção 1). V1 assume 1
 * parking_lot por car_wash na UI, mas o schema não impede mais de um.
 * Acesso restrito pelo middleware 'parking-active'.
 */
class ParkingLotController extends Controller
{
    public function show(): View
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));
        $parkingLot = $carWash->parkingLots()->first();

        $freeSpots = null;
        $occupiedSpots = null;

        if ($parkingLot !== null) {
            $occupiedSpots = $parkingLot->sessions()->where('status', 'open')->count();
            $freeSpots = max(0, $parkingLot->total_spots - $occupiedSpots);
        }

        return view('panel.parking.dashboard', compact('carWash', 'parkingLot', 'freeSpots', 'occupiedSpots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'total_spots' => ['required', 'integer', 'min:1'],
        ]);

        $parkingLot = $carWash->parkingLots()->first();

        if ($parkingLot === null) {
            $carWash->parkingLots()->create($validated);
        } else {
            $parkingLot->update($validated);
        }

        return redirect()->route('panel.parking.sessions.index')->with('success', 'Estacionamento salvo.');
    }
}
