<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

/**
 * Resolve qual gateway está ativo (task-4, seção 3) — Strategy por
 * linha de banco, mesmo padrão do AiServiceFactory do ecossistema
 * Celke. O ADM troca o is_active pelo painel e a próxima chamada de
 * make() já resolve pro novo, sem deploy.
 */
class PaymentGatewayFactory
{
    public static function make(): PaymentGatewayInterface
    {
        $gateway = static::resolveActiveGateway();

        if ($gateway === null) {
            throw new PaymentGatewayNotConfiguredException;
        }

        $serviceClass = $gateway->type->service_class;

        if (! class_exists($serviceClass)) {
            throw new PaymentGatewayNotConfiguredException(
                "Classe de gateway inexistente: {$serviceClass}",
            );
        }

        return new $serviceClass($gateway);
    }

    /**
     * Só o model, sem instanciar a classe — usado ao CRIAR o pedido
     * (pending), pra já gravar qual gateway vai processar antes do
     * pagamento acontecer.
     */
    public static function resolveActiveGateway(): ?PaymentGateway
    {
        $active = PaymentGateway::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        if ($active->count() > 1) {
            // Corrida de "dois ativos ao mesmo tempo": resolve pro mais
            // recente sem derrubar o checkout (task-4, seção 3).
            Log::warning('Mais de um gateway de pagamento ativo; usando o de updated_at mais recente.', [
                'active_ids' => $active->pluck('id')->all(),
            ]);
        }

        return $active->first();
    }
}
