<?php

namespace App\Http\Controllers;

use App\Models\CarWash;
use App\Models\WashRedemption;
use App\Services\Wash\CarWashRatingService;
use App\Services\Wash\WashCancellationRequestService;
use App\Services\Wash\WashRedemptionService;
use App\Services\Wash\WashRedemptionValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fluxo de resgate de lavagem pelo assinante (task-8, seções 2 e 6;
 * escolha de veículo — Passo 0 — integrada na task-15, seção 3).
 */
class WashController extends Controller
{
    public function choose(Request $request): View|RedirectResponse
    {
        $vehicles = $request->user()->vehicles()->where('active', true)->get();

        // Passo 0 (task-15): sem nenhum veículo ativo, redireciona pra
        // cadastrar antes de poder prosseguir.
        if ($vehicles->isEmpty()) {
            return redirect()->route('vehicles.create')
                ->with('error', 'Cadastre um veículo antes de resgatar uma lavagem.');
        }

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

        // Histórico de lavagens do assinante (task-8, seção 3).
        $history = WashRedemption::whereHas(
            'subscriptionCycle.subscription',
            fn ($query) => $query->where('user_id', $request->user()->id),
        )->with(['carWash', 'rating'])->latest('created_at')->paginate(10);

        return view('subscriber.wash.choose', compact('carWashes', 'activeRedemption', 'history', 'vehicles'));
    }

    public function request(Request $request, CarWash $carWash, WashRedemptionService $service): RedirectResponse
    {
        $subscription = $request->user()->subscriptions()->where('status', 'active')->first();

        if ($subscription === null) {
            return back()->with('error', 'Você não tem uma assinatura ativa.');
        }

        $vehicles = $request->user()->vehicles()->where('active', true)->get();

        if ($vehicles->isEmpty()) {
            return redirect()->route('vehicles.create')
                ->with('error', 'Cadastre um veículo antes de resgatar uma lavagem.');
        }

        // 1 veículo só: pula a escolha. 2+: exige vehicle_id explícito
        // (task-15, seção 3).
        if ($vehicles->count() === 1) {
            $vehicle = $vehicles->first();
        } else {
            $vehicle = $vehicles->firstWhere('id', (int) $request->input('vehicle_id'));

            if ($vehicle === null) {
                return back()->with('error', 'Escolha qual veículo vai ser lavado.');
            }
        }

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

    public function rate(Request $request, WashRedemption $washRedemption, CarWashRatingService $service): RedirectResponse
    {
        $this->authorizeOwnership($request, $washRedemption);

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $service->rate($washRedemption, $request->user()->id, $validated['score'], $validated['comment'] ?? null);
        } catch (WashRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('wash.choose')->with('success', 'Avaliação registrada. Obrigado!');
    }

    public function requestCancellation(Request $request, WashRedemption $washRedemption, WashCancellationRequestService $service): RedirectResponse
    {
        $this->authorizeOwnership($request, $washRedemption);

        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        try {
            $service->request($washRedemption, $request->user()->id, $validated['reason']);
        } catch (WashRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('wash.choose')
            ->with('success', 'Solicitação de cancelamento enviada. A plataforma vai analisar.');
    }

    private function authorizeOwnership(Request $request, WashRedemption $washRedemption): void
    {
        $ownsIt = $washRedemption->subscriptionCycle->subscription->user_id === $request->user()->id;

        abort_unless($ownsIt, 403);
    }
}
