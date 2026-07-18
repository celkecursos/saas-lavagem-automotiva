<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Chave pública do PagBank usada pelo PagSeguro.encryptCard() no
 * browser (task-4, seção 5.3). NÃO é credencial copiada do portal —
 * é gerada/consultada via API com o próprio Bearer token:
 *
 *   GET  /public-keys/card  (consulta a existente; 404 se não há)
 *   POST /public-keys {type: card}  (cria — só quando ainda não existe,
 *   POST repetido pode rotacionar a chave à toa)
 *
 * Como a chave não muda a cada request, fica em cache por gateway.
 */
class PagSeguroPublicKeyProvider
{
    public static function for(PaymentGateway $gateway): ?string
    {
        return Cache::remember(
            "pagseguro:public-key:{$gateway->id}",
            now()->addHours(12),
            fn (): ?string => static::fetch($gateway),
        );
    }

    private static function fetch(PaymentGateway $gateway): ?string
    {
        $token = $gateway->credentials['token'] ?? null;

        if (blank($token)) {
            return null;
        }

        $baseUrl = $gateway->sandbox_mode
            ? 'https://sandbox.api.pagseguro.com'
            : 'https://api.pagseguro.com';

        $existing = Http::withToken($token)->get("{$baseUrl}/public-keys/card");

        if ($existing->successful() && filled($existing->json('public_key'))) {
            return $existing->json('public_key');
        }

        $created = Http::withToken($token)->post("{$baseUrl}/public-keys", ['type' => 'card']);

        if ($created->successful() && filled($created->json('public_key'))) {
            return $created->json('public_key');
        }

        Log::warning('Não foi possível obter a chave pública do PagBank.', [
            'payment_gateway_id' => $gateway->id,
            'get_status' => $existing->status(),
            'post_status' => $created->status(),
        ]);

        return null;
    }
}
