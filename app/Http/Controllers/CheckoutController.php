<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payment\PagSeguroGateway;
use App\Services\Payment\PagSeguroPublicKeyProvider;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentGatewayNotConfiguredException;
use App\Services\Subscription\SubscriptionActivator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Tela de checkout embedded (task-4, seção 5.3): formulário de cartão
 * do PRÓPRIO projeto + SDK JS do PagBank. O cartão é criptografado no
 * browser via PagSeguro.encryptCard() — número/CVV nunca chegam ao
 * Laravel. O POST /planos/{plan}/assinar que processa é da task-7.
 */
class CheckoutController extends Controller
{
    public function show(Plan $plan): View
    {
        abort_unless($plan->active, 404);

        $gateway = PaymentGatewayFactory::resolveActiveGateway();

        // Sem gateway ativo: tela amigável, nunca erro 500 (task-7).
        if ($gateway === null) {
            return view('checkout.unavailable', compact('plan'));
        }

        // A chave pública do encryptCard é obtida via API com o próprio
        // token (não é credencial do portal — task-4, seção 5.3). Só ELA
        // vai pra view; o token da API fica no backend.
        $publicKey = PagSeguroPublicKeyProvider::for($gateway);

        if ($publicKey === null) {
            return view('checkout.unavailable', compact('plan'));
        }

        return view('checkout.show', compact('plan', 'publicKey'));
    }

    /**
     * POST /planos/{plan}/assinar (task-7, seção 3) — chamado pelo JS do
     * checkout já com o cartão criptografado no browser, nunca com dado
     * de cartão em texto puro.
     */
    public function store(Request $request, Plan $plan): JsonResponse
    {
        abort_unless($plan->active, 404);

        $user = $request->user();

        // v1: 1 assinatura ativa por vez; trocar de plano é fluxo
        // separado (task-7, seção 5).
        if ($user->subscriptions()->where('status', 'active')->exists()) {
            return response()->json([
                'message' => 'Você já tem uma assinatura ativa. Cancele ou troque de plano antes de assinar outro.',
            ], 422);
        }

        $validated = $request->validate([
            'encrypted_card' => ['required', 'string'],
        ]);

        // Grava o gateway já na criação do order (pending) — nunca
        // deixa null até o markPaid (task-4, seção 3).
        $activeGateway = PaymentGatewayFactory::resolveActiveGateway();

        if ($activeGateway === null) {
            Log::error('Checkout sem gateway de pagamento ativo configurado.', [
                'plan_id' => $plan->id,
                'user_id' => $user->id,
            ]);

            // Não cria subscription/order órfã — erro amigável direto.
            return response()->json([
                'message' => 'Pagamentos indisponíveis no momento. Tente novamente mais tarde.',
            ], 503);
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'incomplete',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'payment_gateway_id' => $activeGateway->id,
            'payable_type' => Subscription::class,
            'payable_id' => $subscription->id,
            'amount_cents' => $plan->price_cents,
            'currency' => 'BRL',
            // O checkout_mode do gateway escolhido PRECISA ser 'embedded'
            // pra assinatura — tokeniza e permite reutilizar depois
            // (task-4, seção 5.1).
            'recurring_type' => 'initial',
            'status' => 'pending',
        ]);

        try {
            $service = PaymentGatewayFactory::make();
        } catch (PaymentGatewayNotConfiguredException $e) {
            // Defesa em profundidade: o gateway foi desativado bem no
            // meio do checkout, depois do passo acima já ter passado.
            Log::error('Gateway ficou indisponível durante o checkout.', ['order_id' => $order->id]);

            return response()->json([
                'message' => 'Pagamentos indisponíveis no momento. Tente novamente mais tarde.',
            ], 503);
        }

        if ($service instanceof PagSeguroGateway) {
            $service->setEncryptedCard($validated['encrypted_card']);
        }

        $result = $service->createCheckout($order);
        $syncStatus = $result->embeddedData['status'] ?? null;

        // A API de Orders do PagBank responde de forma síncrona (PAID/
        // DECLINED na mesma chamada) — não precisamos esperar um webhook
        // pra confirmar o 1º pagamento; a mesma lógica de ativação é
        // reaproveitada aqui e no webhook real (idempotente).
        if ($syncStatus === 'paid') {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'external_reference' => $result->externalReference,
            ]);

            SubscriptionActivator::activateFromInitialOrder($order);

            return response()->json(['redirect' => route('subscription.show')]);
        }

        $order->update([
            'status' => 'failed',
            'external_reference' => $result->externalReference,
        ]);

        return response()->json([
            'message' => $result->embeddedData['failure_reason'] ?? 'Pagamento recusado.',
        ], 422);
    }
}
