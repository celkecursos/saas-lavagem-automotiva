<?php

namespace App\Services\Subscription;

use App\Models\Order;
use App\Models\PaymentMethodToken;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Services\Payment\PaymentGatewayFactory;
use Illuminate\Support\Carbon;

/**
 * Renovação recorrente via cartão salvo (task-7, seção 4) — 100%
 * automática, sem checkout visível. Cada cobrança é um Order NOVO
 * (nunca reaproveita um antigo, mesmo princípio do Celke Payments).
 */
class SubscriptionRenewalService
{
    /**
     * Carência de calendário antes de cancelar por falha de renovação
     * (task-7, seção 4) — contada a partir do current_period_end
     * original, que fica congelado enquanto a assinatura está
     * 'past_due' (só avança quando a cobrança é aprovada).
     */
    private const GRACE_DAYS = 3;

    public function renewDueSubscriptions(): void
    {
        Subscription::query()
            ->whereIn('status', ['active', 'past_due'])
            ->where('current_period_end', '<=', now())
            ->with('plan')
            ->each(fn (Subscription $subscription) => $this->renewOne($subscription));
    }

    private function renewOne(Subscription $subscription): void
    {
        $activeGateway = PaymentGatewayFactory::resolveActiveGateway();

        // Sem gateway ativo: mesma lógica de falha do passo 4 (sem
        // tentar cobrar) — coberto pelo bloco de token abaixo.
        $token = $activeGateway === null ? null : PaymentMethodToken::where([
            'user_id' => $subscription->user_id,
            'payment_gateway_id' => $activeGateway->id,
        ])->first();

        // Sem token pro gateway ATUALMENTE ativo (ex: gateway trocado
        // desde a assinatura inicial): falha direto, sem tentar cobrar
        // (task-7, seção 4, passo 2).
        if ($token === null) {
            $this->handleFailure($subscription);

            return;
        }

        $order = Order::create([
            'user_id' => $subscription->user_id,
            'payment_gateway_id' => $activeGateway->id,
            'payable_type' => Subscription::class,
            'payable_id' => $subscription->id,
            'amount_cents' => $subscription->plan->price_cents,
            'currency' => 'BRL',
            'recurring_type' => 'subsequent',
            'status' => 'pending',
        ]);

        $service = PaymentGatewayFactory::make();
        $result = $service->chargeSavedMethod($order, $token);

        if ($result->status === 'paid') {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'external_reference' => $result->externalReference,
            ]);

            $this->handleSuccess($subscription);

            return;
        }

        $order->update([
            'status' => 'failed',
            'external_reference' => $result->externalReference,
        ]);

        $this->handleFailure($subscription);
    }

    private function handleSuccess(Subscription $subscription): void
    {
        // Troca de plano agendada (pending_plan_id) entra em vigor
        // exatamente aqui — na renovação, nunca no ciclo corrente
        // (task-7, seção 5).
        $plan = $subscription->pending_plan_id !== null
            ? $subscription->pendingPlan
            : $subscription->plan;

        $previousPeriodEnd = $subscription->current_period_end;
        $newPeriodEnd = SubscriptionActivator::nextPeriodEnd($plan->quota_period, $previousPeriodEnd);

        $quotaTotal = $plan->wash_quota;

        if ($plan->rollover_quota) {
            $previousCycle = $subscription->cycles()->latest('period_start')->first();

            if ($previousCycle !== null) {
                $unused = max(0, $previousCycle->quota_total - $previousCycle->quota_used);
                $quotaTotal += $unused;
            }
        }

        $subscription->update([
            'status' => 'active',
            'plan_id' => $plan->id,
            'current_period_start' => $previousPeriodEnd,
            'current_period_end' => $newPeriodEnd,
            'pending_plan_id' => null,
        ]);

        SubscriptionCycle::create([
            'subscription_id' => $subscription->id,
            'period_start' => $previousPeriodEnd,
            'period_end' => $newPeriodEnd,
            'quota_total' => $quotaTotal,
            'quota_used' => 0,
        ]);
    }

    private function handleFailure(Subscription $subscription): void
    {
        $graceDeadline = Carbon::parse($subscription->current_period_end)->addDays(self::GRACE_DAYS);

        $subscription->update([
            'status' => now()->greaterThan($graceDeadline) ? 'canceled' : 'past_due',
        ]);
    }
}
