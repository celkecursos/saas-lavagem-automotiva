<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CRUD de planos (task-11, seção 4). Auditable (task-3): mudança de
 * preço/cota de um plano em uso por assinantes ativos é sensível — só
 * afeta o PRÓXIMO ciclo (subscription_cycles congela quota_total no
 * momento do ciclo, já garantido pelo schema).
 */
class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('features')->orderBy('name')->paginate(15);

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()->route('payment-plans.index')->with('success', 'Plano criado.');
    }

    public function edit(Plan $plan): View
    {
        $plan->load(['features' => fn ($query) => $query->orderBy('sort_order')]);

        return view('admin.plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()->route('payment-plans.edit', $plan)->with('success', 'Plano atualizado.');
    }
}
