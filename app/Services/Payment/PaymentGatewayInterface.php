<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\PaymentMethodToken;
use Illuminate\Http\Request;

/**
 * Contrato que todo gateway concreto implementa (task-4, seção 2).
 * Adicionar um provedor novo = nova classe implementando isto + linha
 * no seeder de payment_gateway_types; Factory e tabelas não mudam.
 */
interface PaymentGatewayInterface
{
    public function createCheckout(Order $order): CheckoutResult;

    public function handleWebhook(Request $request): WebhookResult;

    public function verifySignature(Request $request): bool;

    /**
     * false = gateway não suporta reembolso via API; cai pro processo
     * manual do admin (task-21).
     */
    public function refund(Order $order): bool;

    /**
     * Cobrança automática de renovação com cartão já salvo — NÃO é um
     * checkout (o assinante não vê/faz nada). Ver task-4, seção 5.1,
     * sobre a recorrência "manual" (nós disparamos o run).
     */
    public function chargeSavedMethod(Order $order, PaymentMethodToken $method): ChargeResult;

    /**
     * false = gateway sem suporte a cartão salvo; a task-7 trata como
     * falha de renovação sem sequer tentar cobrar.
     */
    public function supportsSavedCardRecurring(): bool;
}
