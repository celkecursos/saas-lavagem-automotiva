<?php

namespace App\Http\Controllers;

use App\Models\CarWash;
use App\Models\WashRedemption;
use App\Services\Wash\WashRedemptionService;
use App\Services\Wash\WashRedemptionValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fluxo de resgate de lavagem pelo assinante (task-8, seções 2 e 6).
 * Seleção de veículo (Passo 0) chega completa na task-15 — vehicle_id
 * já é aceito aqui se enviado, mas ainda não é obrigatório (nullable
 * no schema até lá, ver task-3, seção 3).
 */
class WashController extends Controller
{
    public function choose(Request $request): View
    {
        // Lava-rápidos aptos a receber resgate: aprovados E com o
        // produto 'clube_lavagem' ativo (task-8, seção 2, passo 1).
        $carWashes = CarWash::query()
            ->where('status', 'approved')
            ->whereHas('productSubscriptions', fn ($query) => $query
                ->where('product', 'clube_lavagem')
                ->where('status', 'active'))
            ->orderBy('name')
            ->get();

        $activeRedemption = $request->user()->subscriptions()
            ->where('status', 'active')
            ->first()
            ?->cycles()->latest('period_start')->first()
            ?->washRedemptions()
            ->where('status', 'requested')
            ->where('code_expires_at', '>', now())
            ->first();

        return view('subscriber.wash.choose', compact('carWashes', 'activeRedemption'));
    }

    public function request(Request $request, CarWash $carWash, WashRedemptionService $service): RedirectResponse
    {
        $subscription = $request->user()->subscriptions()->where('status', 'active')->first();

        if ($subscription === null) {
            return back()->with('error', 'Você não tem uma assinatura ativa.');
        }

        $vehicle = $request->filled('vehicle_id')
            ? $request->user()->vehicles()->whereKey($request->input('vehicle_id'))->first()
            : null;

        try {
            $service->request($subscription, $carWash, $vehicle);
        } catch (WashRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('wash.choose')
            ->with('success', 'Código gerado! Mostre no balcão do lava-rápido.');
    }

    public function cancel(Request $request, WashRedemption $washRedemption): RedirectResponse
    {
        $this->authorizeOwnership($request, $washRedemption);

        abort_unless($washRedemption->status === 'requested', 422);

        $washRedemption->update(['status' => 'canceled']);

        return redirect()->route('wash.choose')->with('success', 'Código cancelado.');
    }

    private function authorizeOwnership(Request $request, WashRedemption $washRedemption): void
    {
        $ownsIt = $washRedemption->subscriptionCycle->subscription->user_id === $request->user()->id;

        abort_unless($ownsIt, 403);
    }
}
