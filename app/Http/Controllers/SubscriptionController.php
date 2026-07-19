<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Notifications\PlanChangeScheduled;
use App\Notifications\SubscriptionCanceled;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Painel do assinante (task-7, seção 6): plano atual, cancelamento e
 * troca de plano agendada.
 */
class SubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        $subscription = $this->currentSubscription($request);
        $orders = $subscription?->orders()->latest('created_at')->get() ?? collect();

        return view('subscriber.subscription', compact('subscription', 'orders'));
    }

    /**
     * Cancelamento voluntário (task-7, seção 5): o acesso continua até
     * current_period_end (o período já pago não é interrompido), só a
     * renovação futura não acontece — diferente de reembolso/chargeback
     * (task-21), que revoga o acesso IMEDIATAMENTE.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $subscription = $this->currentSubscription($request);

        abort_if($subscription === null || $subscription->status === 'canceled', 404);

        $subscription->update(['status' => 'canceled', 'canceled_at' => now()]);

        $request->user()->notify(new SubscriptionCanceled($subscription));

        return redirect()->route('subscription.show')
            ->with('success', 'Assinatura cancelada. Seu acesso continua até o fim do período já pago.');
    }

    /**
     * Troca de plano: v1 sem proporcionalidade, só entra em vigor na
     * PRÓXIMA renovação (task-7, seção 5) — aplicada de fato em
     * SubscriptionRenewalService::handleSuccess.
     */
    public function changePlan(Request $request): RedirectResponse
    {
        $subscription = $this->currentSubscription($request);

        abort_if($subscription === null || $subscription->status !== 'active', 404);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        abort_unless(Plan::whereKey($validated['plan_id'])->where('active', true)->exists(), 422);

        $subscription->update(['pending_plan_id' => $validated['plan_id']]);

        $request->user()->notify(new PlanChangeScheduled($subscription->fresh()));

        return redirect()->route('subscription.show')
            ->with('success', 'Troca de plano agendada pra sua próxima renovação.');
    }

    private function currentSubscription(Request $request)
    {
        return $request->user()->subscriptions()
            ->with(['plan', 'pendingPlan', 'cycles' => fn ($query) => $query->latest('period_start')->limit(1)])
            ->latest()
            ->first();
    }
}
