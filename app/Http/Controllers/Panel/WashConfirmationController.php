<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Services\Wash\WashRedemptionService;
use App\Services\Wash\WashRedemptionValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Confirmação de lavagem pelo funcionário do lava-rápido — a ação mais
 * frequente do balcão (task-8, seção 2, passo 4; task-14, seção 5).
 */
class WashConfirmationController extends Controller
{
    public function show(): View
    {
        return view('panel.wash.confirm');
    }

    public function confirm(Request $request, WashRedemptionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'confirmation_code' => ['required', 'string', 'size:6'],
        ]);

        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        try {
            $redemption = $service->confirm($carWash, $validated['confirmation_code'], $request->user()->id);
        } catch (WashRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('panel.washes.confirm')
            ->with('success', "Lavagem confirmada! Veículo: {$redemption->vehicle?->plate}");
    }
}
