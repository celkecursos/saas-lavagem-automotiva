<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLoyaltyRedemptionRequest;
use App\Models\LoyaltyRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Catálogo de recompensas trocáveis por pontos (task-20, seção 6) —
 * mesmo padrão de payout-plans.*.
 */
class LoyaltyRedemptionController extends Controller
{
    public function index(): View
    {
        $loyaltyRedemptions = LoyaltyRedemption::orderBy('points_cost')->paginate(15);

        return view('admin.loyalty-redemptions.index', compact('loyaltyRedemptions'));
    }

    public function create(): View
    {
        return view('admin.loyalty-redemptions.create');
    }

    public function store(StoreLoyaltyRedemptionRequest $request): RedirectResponse
    {
        LoyaltyRedemption::create($request->validated());

        return redirect()->route('loyalty-redemptions.index')->with('success', 'Recompensa criada.');
    }

    public function edit(LoyaltyRedemption $loyaltyRedemption): View
    {
        return view('admin.loyalty-redemptions.edit', compact('loyaltyRedemption'));
    }

    public function update(StoreLoyaltyRedemptionRequest $request, LoyaltyRedemption $loyaltyRedemption): RedirectResponse
    {
        $loyaltyRedemption->update($request->validated());

        return redirect()->route('loyalty-redemptions.index')->with('success', 'Recompensa atualizada.');
    }
}
