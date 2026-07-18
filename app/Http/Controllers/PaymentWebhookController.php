<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use App\Models\PaymentWebhookEvent;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionActivator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recebe webhooks de qualquer gateway (task-4, seção 2; task-7,
 * seção 3) — genérico por design: resolve o gateway pelo slug do tipo
 * na URL, delega a validação/parsing pra própria classe do gateway.
 * Usado hoje pra chargeback/reembolso (task-21) e serve de base pra
 * gateways futuros que sejam realmente assíncronos (PagBank responde a
 * 1ª cobrança de forma síncrona — ver CheckoutController).
 */
class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gatewayTypeSlug): JsonResponse
    {
        $type = PaymentGatewayType::where('slug', $gatewayTypeSlug)->firstOrFail();

        $gateway = PaymentGateway::where('payment_gateway_type_id', $type->id)
            ->where('is_active', true)
            ->first();

        abort_if($gateway === null, 404);

        $serviceClass = $type->service_class;
        $service = new $serviceClass($gateway);

        if (! $service->verifySignature($request)) {
            return response()->json(['message' => 'invalid signature'], 401);
        }

        $result = $service->handleWebhook($request);

        // Idempotência real: reenviar o MESMO webhook (mesmo
        // gateway+external_reference+event_type) não reprocessa nada
        // (task-4, seção 1; task-13, seção 2.1).
        $alreadyProcessed = PaymentWebhookEvent::where([
            'payment_gateway_id' => $gateway->id,
            'external_reference' => $result->externalReference,
            'event_type' => $result->eventType,
        ])->exists();

        if ($alreadyProcessed) {
            return response()->json(['message' => 'already processed']);
        }

        PaymentWebhookEvent::create([
            'payment_gateway_id' => $gateway->id,
            'event_type' => $result->eventType,
            'external_reference' => $result->externalReference,
            'payload' => $request->json()->all(),
            'processed_at' => now(),
        ]);

        $order = $this->resolveOrder($result->orderReference);

        if ($order === null) {
            return response()->json(['message' => 'order not found']);
        }

        $order->update([
            'status' => $result->status,
            'paid_at' => $result->status === 'paid' ? now() : $order->paid_at,
        ]);

        if ($result->status === 'paid' && $order->payable_type === Subscription::class) {
            SubscriptionActivator::activateFromInitialOrder($order);
        }

        return response()->json(['message' => 'ok']);
    }

    private function resolveOrder(string $orderReference): ?Order
    {
        // Convenção usada pelo PagSeguroGateway: "order-{id}".
        if (! str_starts_with($orderReference, 'order-')) {
            return null;
        }

        return Order::find((int) str_replace('order-', '', $orderReference));
    }
}
