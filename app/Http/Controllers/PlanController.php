<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

/**
 * Vitrine pública de planos (task-7, seção 2).
 */
class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->where('active', true)
            ->with(['features' => fn ($query) => $query->where('active', true)->orderBy('sort_order')])
            ->get();

        // Plano atual do usuário logado (badge "seu plano atual" —
        // task-6: active -> success).
        $currentPlanId = auth()->user()
            ?->subscriptions()
            ->where('status', 'active')
            ->value('plan_id');

        return view('subscriber.plans', compact('plans', 'currentPlanId'));
    }
}
