<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Refund\RefundService;
use App\Services\Refund\RefundValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pedido do assinante (task-7, seção 6, "histórico de pedidos/
 * pagamentos"; task-21, seção 2, passo 1 — solicitação de reembolso
 * self-service).
 */
class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['paymentGateway', 'refundRequest']);

        return view('subscriber.order-show', compact('order'));
    }

    public function requestRefund(Request $request, Order $order, RefundService $service): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $service->requestSelfService($order, $validated['reason'], $request->user());
        } catch (RefundValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('order.show', $order)->with('success', 'Reembolso solicitado.');
    }
}
