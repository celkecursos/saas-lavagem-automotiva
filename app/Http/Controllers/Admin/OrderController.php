<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Refund\RefundService;
use App\Services\Refund\RefundValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Auditoria/suporte de pagamentos (task-11, seção 4) — mesmo padrão do
 * Celke Payments (order-managers). Ação de reembolso (task-21, seção
 * 2, passo 2): admin pede em nome do assinante, fora da janela de
 * self-service ou por qualquer motivo de suporte.
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['user', 'paymentGateway'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'paymentGateway', 'payable', 'refundRequest']);

        return view('admin.orders.show', compact('order'));
    }

    public function refund(Request $request, Order $order, RefundService $service): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $service->requestByAdmin($order, $validated['reason'], $request->user());
        } catch (RefundValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Reembolso solicitado.');
    }
}
