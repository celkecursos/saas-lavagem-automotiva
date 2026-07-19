<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\WashRedemption;
use App\Services\Wash\WashCancellationRequestService;
use App\Services\Wash\WashRedemptionValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Histórico de lavagens confirmadas naquele lava-rápido — conferência
 * antes do repasse (task-8, seção 4; liga com task-9).
 */
class WashHistoryController extends Controller
{
    public function index(): View
    {
        $redemptions = WashRedemption::where('car_wash_id', session('current_car_wash_id'))
            ->with(['vehicle', 'confirmedBy'])
            ->latest('created_at')
            ->paginate(15);

        return view('panel.wash.index', compact('redemptions'));
    }

    /**
     * Funcionário/dono do lava-rápido também pode solicitar cancelamento
     * de uma lavagem já confirmada (ex: confirmou o código da pessoa
     * errada) — mesma regra do lado do assinante (task-8, seção 2, §8).
     */
    public function requestCancellation(Request $request, WashRedemption $washRedemption, WashCancellationRequestService $service): RedirectResponse
    {
        abort_unless($washRedemption->car_wash_id === (int) session('current_car_wash_id'), 403);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        try {
            $service->request($washRedemption, $request->user()->id, $validated['reason']);
        } catch (WashRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('panel.washes.index')
            ->with('success', 'Solicitação de cancelamento enviada. A plataforma vai analisar.');
    }
}
