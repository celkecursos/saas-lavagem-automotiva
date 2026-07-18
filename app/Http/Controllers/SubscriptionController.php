<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Painel do assinante (task-7, seção 6). Detalhes de cancelamento/troca
 * de plano/histórico chegam nos próximos commits desta task.
 */
class SubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        $subscription = $request->user()->subscriptions()
            ->with(['plan', 'cycles' => fn ($query) => $query->latest('period_start')->limit(1)])
            ->latest()
            ->first();

        return view('subscriber.subscription', compact('subscription'));
    }
}
