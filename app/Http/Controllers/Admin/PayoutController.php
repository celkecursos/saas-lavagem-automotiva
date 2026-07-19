<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Notifications\PayoutPaid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Painel admin de repasses (task-9, seção 3). Pagamento em si
 * (transferência, PIX) acontece FORA do sistema — o admin só registra
 * aqui.
 */
class PayoutController extends Controller
{
    public function index(Request $request): View
    {
        $payouts = Payout::with('carWash')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payouts.index', compact('payouts'));
    }

    public function show(Payout $payout): View
    {
        $payout->load(['carWash', 'items.washRedemption']);

        return view('admin.payouts.show', compact('payout'));
    }

    public function markPaid(Request $request, Payout $payout): RedirectResponse
    {
        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:255'],
        ], [
            'payment_reference.required' => 'Informe a referência da transferência (ex: nº da operação bancária).',
        ]);

        $payout->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $validated['payment_reference'],
        ]);

        $owners = $payout->carWash->users()->wherePivot('role', 'owner')->get();
        Notification::send($owners, new PayoutPaid($payout));

        return redirect()->route('payouts.show', $payout)->with('success', 'Repasse marcado como pago.');
    }

    public function markFailed(Payout $payout): RedirectResponse
    {
        // Não reprocessa sozinho — intervenção manual do admin no
        // próximo lote (task-9, seção 3).
        $payout->update(['status' => 'failed']);

        return redirect()->route('payouts.show', $payout)->with('success', 'Repasse marcado como falhou.');
    }
}
