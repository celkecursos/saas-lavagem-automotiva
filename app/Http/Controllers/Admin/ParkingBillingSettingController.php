<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingBillingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Configuração da monetização do estacionamento — % e antifraude
 * (task-3, seção 4.1; task-10, seção 8). Singleton.
 */
class ParkingBillingSettingController extends Controller
{
    public function edit(): View
    {
        $settings = ParkingBillingSetting::current();

        return view('admin.parking-billing-settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_turns_per_day_per_spot' => ['required', 'integer', 'min:1'],
        ]);

        ParkingBillingSetting::current()->update($validated);

        return redirect()->route('parking-billing-settings.edit')->with('success', 'Configurações salvas.');
    }
}
