<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Meus veículos" do assinante (task-15, seção 2).
 */
class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = $request->user()->vehicles()->where('active', true)->latest()->get();

        return view('subscriber.vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('subscriber.vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $request->user()->vehicles()->create($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Veículo cadastrado.');
    }

    public function edit(Request $request, Vehicle $vehicle): View
    {
        $this->authorizeOwnership($request, $vehicle);

        return view('subscriber.vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeOwnership($request, $vehicle);

        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Veículo atualizado.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeOwnership($request, $vehicle);

        // Soft delete: histórico de lavagens continua válido (task-15,
        // seção 2), só some da lista pra escolher num novo resgate.
        $vehicle->update(['active' => false]);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Veículo removido.');
    }

    public function washes(Request $request, Vehicle $vehicle): View
    {
        $this->authorizeOwnership($request, $vehicle);

        $redemptions = $vehicle->washRedemptions()->with('carWash')->latest('created_at')->paginate(10);

        return view('subscriber.vehicles.washes', compact('vehicle', 'redemptions'));
    }

    private function authorizeOwnership(Request $request, Vehicle $vehicle): void
    {
        abort_unless($vehicle->user_id === $request->user()->id, 403);
    }
}
