<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visão rápida de assinaturas por plano/status (task-11, seção 4) —
 * complementar à visão 360° de usuário da task-22. Sem ação de edição
 * direta aqui; mudanças passam pelos fluxos próprios (task-7) ou
 * suporte via ação específica, se surgir demanda real.
 */
class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('plan_id'), fn ($query, $planId) => $query->where('plan_id', $planId))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $plans = \App\Models\Plan::orderBy('name')->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load([
            'user',
            'plan',
            'cycles' => fn ($query) => $query->latest('period_start'),
            'orders' => fn ($query) => $query->latest('created_at'),
        ]);

        $washRedemptions = \App\Models\WashRedemption::whereIn(
            'subscription_cycle_id',
            $subscription->cycles->pluck('id'),
        )->with('carWash')->latest('created_at')->get();

        return view('admin.subscriptions.show', compact('subscription', 'washRedemptions'));
    }
}
