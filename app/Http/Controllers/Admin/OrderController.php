<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Auditoria/suporte de pagamentos (task-11, seção 4) — mesmo padrão do
 * Celke Payments (order-managers). Ação de reembolso chega na task-21.
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
        $order->load(['user', 'paymentGateway', 'payable']);

        return view('admin.orders.show', compact('order'));
    }
}
