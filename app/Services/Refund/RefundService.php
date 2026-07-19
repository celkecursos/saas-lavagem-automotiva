<?php

namespace App\Services\Refund;

use App\Models\Order;
use App\Models\OrderRefundRequest;
use App\Models\ParkingBillingCharge;
use App\Models\RefundSetting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Reembolso (task-21, seção 2) — pedir já é a decisão (self-service e
 * admin auto-aprovam na criação, sem 'pending'); só falta processar de
 * fato no gateway. Chargeback (seção 3) NÃO passa por aqui — chega só
 * via webhook, sem solicitação prévia; ver PaymentWebhookController.
 */
class RefundService
{
    private const SELF_SERVICE_WINDOW_DAYS = 7;

    public function requestSelfService(Order $order, string $reason, User $user): OrderRefundRequest
    {
        $this->assertSelfServiceEligible($order);

        return $this->createAndProcess($order, $reason, $user, 'self_service');
    }

    public function requestByAdmin(Order $order, string $reason, User $admin): OrderRefundRequest
    {
        if ($order->status !== 'paid') {
            throw new RefundValidationException('Só é possível reembolsar um pedido pago.');
        }

        if ($this->hasExistingRequest($order)) {
            throw new RefundValidationException('Já existe uma solicitação de reembolso pra esse pedido.');
        }

        return $this->createAndProcess($order, $reason, $admin, 'admin');
    }

    /**
     * Confirmação manual de um failed_manual (task-21, seção 2, passo
     * 3) — o admin já estornou por fora (ex: painel do próprio
     * gateway); aqui só fecha o ciclo.
     */
    public function markProcessedManually(OrderRefundRequest $request): void
    {
        $request->update(['status' => 'processed', 'processed_at' => now()]);

        $order = $request->order;
        $order->update(['status' => 'refunded']);

        $this->revokeAccess($order, 'refund');
    }

    public function isSelfServiceEligible(Order $order): bool
    {
        try {
            $this->assertSelfServiceEligible($order);

            return true;
        } catch (RefundValidationException) {
            return false;
        }
    }

    /**
     * Passo 4 (task-21, seção 2) — roda SEMPRE que order.status vira
     * 'refunded'/'chargeback', independente do caminho (RefundService
     * ou webhook de chargeback direto).
     */
    public function revokeAccess(Order $order, string $reason): void
    {
        if ($order->payable_type === Subscription::class) {
            $order->payable?->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'canceled_reason' => $reason,
            ]);
        } elseif ($order->payable_type === ParkingBillingCharge::class) {
            $order->payable?->update(['status' => $reason === 'chargeback' ? 'chargeback' : 'refunded']);
        }
    }

    private function assertSelfServiceEligible(Order $order): void
    {
        if ($order->status !== 'paid') {
            throw new RefundValidationException('Só é possível solicitar reembolso de um pedido pago.');
        }

        if ($this->hasExistingRequest($order)) {
            throw new RefundValidationException('Já existe uma solicitação de reembolso pra esse pedido.');
        }

        if (! $this->withinSelfServiceWindow($order)) {
            throw new RefundValidationException('O prazo pra solicitar reembolso desse pedido já passou.');
        }
    }

    private function withinSelfServiceWindow(Order $order): bool
    {
        if ($order->paid_at === null) {
            return false;
        }

        $settings = RefundSetting::current();

        $windowDays = self::SELF_SERVICE_WINDOW_DAYS;

        if ($settings->extended_self_service_enabled && $settings->extended_self_service_until_days !== null) {
            $windowDays = max($windowDays, $settings->extended_self_service_until_days);
        }

        return Carbon::now()->lessThanOrEqualTo($order->paid_at->copy()->addDays($windowDays));
    }

    private function hasExistingRequest(Order $order): bool
    {
        return OrderRefundRequest::where('order_id', $order->id)->exists();
    }

    private function createAndProcess(Order $order, string $reason, User $user, string $initiatedBy): OrderRefundRequest
    {
        $request = OrderRefundRequest::create([
            'order_id' => $order->id,
            'requested_by_user_id' => $user->id,
            'initiated_by' => $initiatedBy,
            'reason' => $reason,
            'status' => 'approved',
            'requested_at' => now(),
        ]);

        $this->process($request);

        return $request->fresh();
    }

    private function process(OrderRefundRequest $request): void
    {
        $order = $request->order;

        $gateway = $order->paymentGateway;

        if ($gateway === null) {
            $request->update(['status' => 'failed_manual']);

            return;
        }

        // Reembolsa pelo MESMO gateway que processou o pedido
        // originalmente (order.payment_gateway_id) — não
        // necessariamente o gateway ativo hoje (task-4, seção 3, sobre
        // troca de gateway sem quebrar histórico).
        $serviceClass = $gateway->type->service_class;
        $service = new $serviceClass($gateway);
        $processed = $service->refund($order);

        if (! $processed) {
            $request->update(['status' => 'failed_manual']);

            return;
        }

        $request->update(['status' => 'processed', 'processed_at' => now()]);
        $order->update(['status' => 'refunded']);

        $this->revokeAccess($order, 'refund');
    }
}
