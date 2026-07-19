<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderRefundRequest;
use App\Services\Refund\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fila de reembolsos que o gateway não processou via API (task-21,
 * seção 2, passo 3) — o admin estorna por fora (ex: painel do próprio
 * gateway) e só então confirma aqui.
 */
class OrderRefundRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = OrderRefundRequest::with(['order.user'])
            ->where('status', 'failed_manual')
            ->latest('requested_at')
            ->paginate(15);

        return view('admin.order-refund-requests.index', compact('requests'));
    }

    public function markProcessed(OrderRefundRequest $orderRefundRequest, RefundService $service): RedirectResponse
    {
        abort_unless($orderRefundRequest->status === 'failed_manual', 422);

        $service->markProcessedManually($orderRefundRequest);

        return redirect()->route('order-refund-requests.index')->with('success', 'Reembolso marcado como processado.');
    }
}
