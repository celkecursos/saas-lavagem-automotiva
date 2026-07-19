<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePayoutPlanRequest;
use App\Models\PayoutPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Catálogo de planos de repasse (task-9, seção 1 / task-11, seção 4).
 * Auditable: mudar um valor afeta o cálculo de todos os lava-rápidos
 * que escolheram aquele plano (task-3, seção 5).
 */
class PayoutPlanController extends Controller
{
    public function index(): View
    {
        $payoutPlans = PayoutPlan::orderBy('category')->orderBy('level')->paginate(15);

        return view('admin.payout-plans.index', compact('payoutPlans'));
    }

    public function create(): View
    {
        return view('admin.payout-plans.create');
    }

    public function store(StorePayoutPlanRequest $request): RedirectResponse
    {
        PayoutPlan::create($request->validated());

        return redirect()->route('payout-plans.index')->with('success', 'Plano de repasse criado.');
    }

    public function edit(PayoutPlan $payoutPlan): View
    {
        return view('admin.payout-plans.edit', ['payoutPlan' => $payoutPlan]);
    }

    public function update(StorePayoutPlanRequest $request, PayoutPlan $payoutPlan): RedirectResponse
    {
        $payoutPlan->update($request->validated());

        return redirect()->route('payout-plans.index')->with('success', 'Plano de repasse atualizado.');
    }
}
