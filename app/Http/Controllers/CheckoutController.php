<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Payment\PaymentGatewayFactory;
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

        // Só a chave PÚBLICA vai pra view (usada pelo encryptCard no
        // browser) — o token/segredo da API fica só no backend.
        return view('checkout.show', [
            'plan' => $plan,
            'publicKey' => $gateway->credentials['public_key'] ?? '',
        ]);
    }
}
