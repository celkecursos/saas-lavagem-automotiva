<?php

namespace App\Services\Wash;

use App\Models\CarWash;
use App\Models\Subscription;
use App\Models\SubscriptionCycle;
use App\Models\Vehicle;
use App\Models\WashRedemption;
use Illuminate\Support\Str;

/**
 * Regras de negócio do resgate de lavagem (task-8, seção 2). O código
 * gera sem debitar cota — só a confirmação do funcionário debita
 * (task-8, seção 2, passos 2-4).
 */
class WashRedemptionService
{
    /**
     * @throws WashRedemptionValidationException
     */
    public function request(Subscription $subscription, CarWash $carWash, ?Vehicle $vehicle = null): WashRedemption
    {
        if ($subscription->status !== 'active') {
            throw new WashRedemptionValidationException('Sua assinatura não está ativa.');
        }

        $cycle = $subscription->cycles()->latest('period_start')->first();

        if ($cycle === null || $cycle->quota_used >= $cycle->quota_total) {
            throw new WashRedemptionValidationException('Você não tem cota disponível neste ciclo.');
        }

        $this->assertNoOtherPendingCode($subscription->user_id, $cycle);
        $this->assertDailyLimitNotReached($subscription, $carWash, $cycle);

        return WashRedemption::create([
            'subscription_cycle_id' => $cycle->id,
            'car_wash_id' => $carWash->id,
            'vehicle_id' => $vehicle?->id,
            'confirmation_code' => static::generateCode(),
            'code_expires_at' => now()->addMinutes(15),
            'status' => 'requested',
        ]);
    }

    /**
     * Evita gerar vários códigos válidos ao mesmo tempo (task-8, §2).
     *
     * @throws WashRedemptionValidationException
     */
    private function assertNoOtherPendingCode(int $userId, SubscriptionCycle $cycle): void
    {
        $hasPending = WashRedemption::where('subscription_cycle_id', $cycle->id)
            ->where('status', 'requested')
            ->where('code_expires_at', '>', now())
            ->exists();

        if ($hasPending) {
            throw new WashRedemptionValidationException('Você já tem um código válido gerado. Use-o ou aguarde expirar.');
        }
    }

    /**
     * @throws WashRedemptionValidationException
     */
    private function assertDailyLimitNotReached(Subscription $subscription, CarWash $carWash, SubscriptionCycle $cycle): void
    {
        $limit = $subscription->plan->max_redemptions_per_day_per_car_wash;

        if ($limit === null) {
            return;
        }

        $usedToday = WashRedemption::where('subscription_cycle_id', $cycle->id)
            ->where('car_wash_id', $carWash->id)
            ->where('status', 'completed')
            ->whereDate('redeemed_at', now()->toDateString())
            ->count();

        if ($usedToday >= $limit) {
            throw new WashRedemptionValidationException('Você atingiu o limite de lavagens hoje neste lava-rápido.');
        }
    }

    /**
     * Confirmação pelo funcionário no local (task-8, seção 2, passo 4)
     * — só aqui a cota é efetivamente debitada.
     *
     * @throws WashRedemptionValidationException
     */
    public function confirm(CarWash $carWash, string $code, int $confirmedByUserId): WashRedemption
    {
        $redemption = WashRedemption::where('confirmation_code', $code)
            // Nunca aceita código de outro lava-rápido (task-8, §2, passo 4).
            ->where('car_wash_id', $carWash->id)
            ->where('status', 'requested')
            ->where('code_expires_at', '>', now())
            ->first();

        if ($redemption === null) {
            throw new WashRedemptionValidationException('Código inválido, já usado ou expirado.');
        }

        $redemption->update([
            'status' => 'completed',
            'redeemed_at' => now(),
            'confirmed_by_user_id' => $confirmedByUserId,
        ]);

        $redemption->subscriptionCycle()->increment('quota_used');

        return $redemption;
    }

    private static function generateCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (WashRedemption::where('confirmation_code', $code)
            ->where('status', 'requested')
            ->where('code_expires_at', '>', now())
            ->exists());

        return $code;
    }
}
