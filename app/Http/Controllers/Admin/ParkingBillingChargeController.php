<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingBillingCharge;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fila de cobranças do estacionamento, filtro por flagged_for_review
 * (task-10, seção 8) — revisão manual do antifraude.
 */
class ParkingBillingChargeController extends Controller
{
    public function index(Request $request): View
    {
        $charges = ParkingBillingCharge::with('carWash')
            ->when($request->boolean('flagged'), fn ($query) => $query->where('flagged_for_review', true))
            ->latest('period_start')
            ->paginate(15)
            ->withQueryString();

        return view('admin.parking-billing-charges.index', compact('charges'));
    }

    public function show(ParkingBillingCharge $parkingBillingCharge): View
    {
        $parkingBillingCharge->load(['carWash', 'order']);

        return view('admin.parking-billing-charges.show', ['charge' => $parkingBillingCharge]);
    }
}
