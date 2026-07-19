<?php

namespace App\Services\Subscription;

use App\Models\Order;
use App\Models\PaymentMethodToken;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Notifications\SubscriptionCanceled;
use App\Notifications\SubscriptionRenewalFailed;
use App\Notifications\SubscriptionRenewed;
use App\Services\Loyalty\AchievementChecker;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Referral\ReferralRewardGranter;
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
            'amount_cents' => $this->renewalAmountCents($subscription),
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

    /**
     * Desconto de fidelidade (task-20, seção 4) resgatado na loja —
     * aplicado no cálculo do PRÓXIMO order de renovação, zerado logo
     * abaixo em handleSuccess() pra não acumular.
     */
    private function renewalAmountCents(Subscription $subscription): int
    {
        $price = $subscription->plan->price_cents;

        if ($subscription->pending_renewal_discount_percent === null) {
            return $price;
        }

        $discount = (float) $subscription->pending_renewal_discount_percent;

        return (int) round($price * (1 - $discount / 100));
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
            'pending_renewal_discount_percent' => null,
        ]);

        $cycle = SubscriptionCycle::create([
            'subscription_id' => $subscription->id,
            'period_start' => $previousPeriodEnd,
            'period_end' => $newPeriodEnd,
            'quota_total' => $quotaTotal,
            'quota_used' => 0,
        ]);

        // Mesmo ponto de código que concede o bônus na ativação inicial
        // (task-16, seção 2, passo 4) — todo ciclo novo pro indicador.
        ReferralRewardGranter::grantPendingRewardsFor($cycle);

        $subscription->user->notify(new SubscriptionRenewed($subscription->fresh()));

        AchievementChecker::checkMembershipAnniversary($subscription->user);
    }

    private function handleFailure(Subscription $subscription): void
    {
        $graceDeadline = Carbon::parse($subscription->current_period_end)->addDays(self::GRACE_DAYS);
        $canceled = now()->greaterThan($graceDeadline);

        $subscription->update([
            'status' => $canceled ? 'canceled' : 'past_due',
            'canceled_at' => $canceled ? now() : null,
        ]);

        $subscription->user->notify(
            $canceled ? new SubscriptionCanceled($subscription) : new SubscriptionRenewalFailed($subscription),
        );
    }
}
