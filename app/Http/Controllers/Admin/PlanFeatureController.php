<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Vantagens de marketing (plan_features) — gerenciadas DENTRO da tela
 * payment-plans.edit, não uma tela separada (task-11, seção 4).
 * Puramente descritivo: NÃO é Auditable (diferente dos campos
 * funcionais do plano).
 */
class PlanFeatureController extends Controller
{
    public function store(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate(['label' => ['required', 'string', 'max:255']]);

        $plan->features()->create([
            'label' => $validated['label'],
            'sort_order' => $plan->features()->max('sort_order') + 1,
            'active' => true,
        ]);

        return redirect()->route('payment-plans.edit', $plan)->with('success', 'Vantagem adicionada.');
    }

    public function update(Request $request, Plan $plan, PlanFeature $feature): RedirectResponse
    {
        abort_unless($feature->plan_id === $plan->id, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        $feature->update($validated);

        return redirect()->route('payment-plans.edit', $plan)->with('success', 'Vantagem atualizada.');
    }

    public function destroy(Plan $plan, PlanFeature $feature): RedirectResponse
    {
        abort_unless($feature->plan_id === $plan->id, 404);

        $feature->delete();

        return redirect()->route('payment-plans.edit', $plan)->with('success', 'Vantagem removida.');
    }

    /**
     * Reordena com o item ADJACENTE (troca sort_order) — simples o
     * bastante pra uma lista de poucos itens por plano.
     */
    public function move(Request $request, Plan $plan, PlanFeature $feature): RedirectResponse
    {
        abort_unless($feature->plan_id === $plan->id, 404);

        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        $siblings = $plan->features()->orderBy('sort_order')->get();
        $index = $siblings->search(fn (PlanFeature $item) => $item->id === $feature->id);
        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex < 0 || $swapIndex >= $siblings->count()) {
            return redirect()->route('payment-plans.edit', $plan);
        }

        $sibling = $siblings[$swapIndex];
        [$featureOrder, $siblingOrder] = [$feature->sort_order, $sibling->sort_order];

        $feature->update(['sort_order' => $siblingOrder]);
        $sibling->update(['sort_order' => $featureOrder]);

        return redirect()->route('payment-plans.edit', $plan);
    }
}
