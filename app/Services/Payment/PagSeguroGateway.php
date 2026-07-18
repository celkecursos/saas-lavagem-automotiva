<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\PaymentMethodToken;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Gateway concreto v1: PagBank/PagSeguro via API de Orders, com
 * recorrência "manual" — nós disparamos cada cobrança (task-4, seções
 * 5 e 5.1). Sem SDK Composer oficial: API REST com o Http client.
 */
class PagSeguroGateway implements PaymentGatewayInterface
{
    /**
     * Blob card.encrypted gerado no BROWSER via PagSeguro.encryptCard()
     * (task-4, seção 5.3) — número/CVV nunca chegam aqui em texto puro.
     * Setado pelo controller do checkout (task-7) antes de createCheckout.
     */
    private ?string $encryptedCard = null;

    public function __construct(private PaymentGateway $gateway) {}

    public function setEncryptedCard(?string $encryptedCard): void
    {
        $this->encryptedCard = $encryptedCard;
    }

    /**
     * POST na API de Orders. recurring_type='initial': corpo inclui
     * card.encrypted + recurring.type=INITIAL + store=true; o card.id
     * devolvido é gravado em payment_method_tokens pra renovação
     * automática (task-4, seção 5).
     */
    public function createCheckout(Order $order): CheckoutResult
    {
        $isInitial = $order->recurring_type === 'initial';

        $cardPayload = [
            'encrypted' => $this->encryptedCard,
            'store' => $isInitial,
        ];

        if ($isInitial) {
            $cardPayload['recurring'] = ['type' => 'INITIAL'];
        }

        $response = $this->http()->post($this->baseUrl().'/orders', [
            'reference_id' => $this->orderReference($order),
            'customer' => $this->customerPayload($order),
            'items' => [[
                'name' => "Pedido #{$order->id}",
                'quantity' => 1,
                'unit_amount' => $order->amount_cents,
            ]],
            'charges' => [[
                'reference_id' => $this->orderReference($order),
                'description' => "Pedido #{$order->id}",
                'amount' => ['value' => $order->amount_cents, 'currency' => $order->currency],
                'payment_method' => [
                    'type' => 'CREDIT_CARD',
                    'installments' => 1,
                    'capture' => true,
                    'card' => $cardPayload,
                ],
            ]],
        ]);

        $charge = $response->json('charges.0');
        $paid = ($charge['status'] ?? null) === 'PAID';

        if ($paid && $isInitial && filled($charge['payment_method']['card']['id'] ?? null)) {
            PaymentMethodToken::create([
                'user_id' => $order->user_id,
                'payment_gateway_id' => $this->gateway->id,
                'token' => $charge['payment_method']['card']['id'],
                'brand' => $charge['payment_method']['card']['brand'] ?? null,
                'last_four' => $charge['payment_method']['card']['last_digits'] ?? null,
            ]);
        }

        return new CheckoutResult(
            mode: 'embedded',
            embeddedData: [
                'status' => $paid ? 'paid' : 'failed',
                'failure_reason' => $paid ? null : ($charge['payment_response']['message'] ?? 'Pagamento recusado'),
            ],
            externalReference: $charge['id'] ?? null,
        );
    }

    /**
     * Cobrança de renovação com o card.id salvo + recurring SUBSEQUENT —
     * implementada no commit seguinte da task-4 (commit 7).
     */
    public function chargeSavedMethod(Order $order, PaymentMethodToken $method): ChargeResult
    {
        throw new \LogicException('chargeSavedMethod é implementado no commit 7 da task-4.');
    }

    public function supportsSavedCardRecurring(): bool
    {
        return true;
    }

    /**
     * O webhook do PagBank ecoa o objeto do pedido: reference_id é a
     * NOSSA referência ("order-{id}") e charges[0].status o resultado.
     */
    public function handleWebhook(Request $request): WebhookResult
    {
        $payload = $request->json()->all();
        $charge = $payload['charges'][0] ?? [];
        $chargeStatus = $charge['status'] ?? '';

        $refunded = (int) ($charge['amount']['summary']['refunded'] ?? 0) > 0;

        $status = match (true) {
            $chargeStatus === 'PAID' => 'paid',
            in_array($chargeStatus, ['CHARGED_BACK', 'CHARGEBACK'], true) => 'chargeback',
            $chargeStatus === 'CANCELED' && $refunded => 'refunded',
            default => 'failed',
        };

        return new WebhookResult(
            status: $status,
            externalReference: $charge['id'] ?? '',
            orderReference: $payload['reference_id'] ?? ($charge['reference_id'] ?? ''),
            eventType: $chargeStatus,
        );
    }

    /**
     * x-authenticity-token = SHA256("{token}-{payload bruto}") — o corpo
     * TEM que ser usado exatamente como recebido (task-4, seção 5).
     */
    public function verifySignature(Request $request): bool
    {
        $received = (string) $request->header('x-authenticity-token', '');

        if ($received === '') {
            return false;
        }

        $expected = hash('sha256', $this->token().'-'.$request->getContent());

        return hash_equals($expected, $received);
    }

    /**
     * POST /charges/{id}/cancel — true se processou; false cai pro
     * processo manual do admin (task-21).
     */
    public function refund(Order $order): bool
    {
        if (blank($order->external_reference)) {
            return false;
        }

        return $this->http()
            ->post($this->baseUrl()."/charges/{$order->external_reference}/cancel", [
                'amount' => ['value' => $order->amount_cents],
            ])
            ->successful();
    }

    private function http(): PendingRequest
    {
        return Http::withToken($this->token())->acceptJson();
    }

    private function token(): string
    {
        return $this->gateway->credentials['token'] ?? '';
    }

    private function baseUrl(): string
    {
        return $this->gateway->sandbox_mode
            ? 'https://sandbox.api.pagseguro.com'
            : 'https://api.pagseguro.com';
    }

    private function orderReference(Order $order): string
    {
        return "order-{$order->id}";
    }

    /**
     * @return array<string, mixed>
     */
    private function customerPayload(Order $order): array
    {
        return [
            'name' => $order->user->name,
            'email' => $order->user->email,
            // CPF obrigatório na API de Orders; fallback de sandbox
            // enquanto o cadastro não coleta o campo (task-7).
            'tax_id' => preg_replace('/\D/', '', $order->user->cpf ?? '') ?: '12345678909',
        ];
    }
}
