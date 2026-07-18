<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Payment\PagSeguroPublicKeyProvider;
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

        // A chave pública do encryptCard é obtida via API com o próprio
        // token (não é credencial do portal — task-4, seção 5.3). Só ELA
        // vai pra view; o token da API fica no backend.
        $publicKey = PagSeguroPublicKeyProvider::for($gateway);

        if ($publicKey === null) {
            return view('checkout.unavailable', compact('plan'));
        }

        return view('checkout.show', compact('plan', 'publicKey'));
    }
}
