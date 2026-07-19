<?php

namespace App\Services\Loyalty;

use App\Models\Achievement;
use App\Models\LoyaltyPointsLedgerEntry;
use App\Models\ReferralReward;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\WashRedemption;
use App\Notifications\AchievementUnlocked;

/**
 * Checagem de conquistas chamada diretamente dos pontos do código que
 * já disparam os eventos relevantes (task-20, seção 3) — mesmo padrão
 * do resto do projeto (ex: ReferralRewardGranter), sem um sistema de
 * Event/Listener próprio. `code` de cada Achievement NÃO é dado-driven:
 * adicionar uma conquista nova exige uma checagem nova aqui.
 */
class AchievementChecker
{
    public static function checkWashMilestones(User $user): void
    {
        $completedCount = WashRedemption::whereHas(
            'subscriptionCycle.subscription',
            fn ($query) => $query->where('user_id', $user->id),
        )->where('status', 'completed')->count();

        if ($completedCount >= 1) {
            static::unlock($user, 'first_wash');
        }
        if ($completedCount >= 10) {
            static::unlock($user, '10_washes');
        }
        if ($completedCount >= 50) {
            static::unlock($user, '50_washes');
        }
    }

    public static function checkRatings(User $user): void
    {
        $ratingsCount = $user->carWashRatings()->count();

        if ($ratingsCount >= 5) {
            static::unlock($user, '5_ratings');
        }
    }

    public static function checkReferrals(User $user): void
    {
        $grantedCount = ReferralReward::where('referrer_user_id', $user->id)
            ->where('status', 'granted')
            ->count();

        if ($grantedCount >= 3) {
            static::unlock($user, '3_referrals');
        }
    }

    /**
     * Compara a subscriptions mais antiga do usuário — não a que
     * acabou de renovar/ativar (task-20, seção 3).
     */
    public static function checkMembershipAnniversary(User $user): void
    {
        $oldest = Subscription::where('user_id', $user->id)->oldest('created_at')->first();

        if ($oldest !== null && $oldest->created_at->addYear()->lessThanOrEqualTo(now())) {
            static::unlock($user, '1_year_member');
        }
    }

    private static function unlock(User $user, string $code): void
    {
        $achievement = Achievement::where('code', $code)->where('active', true)->first();

        if ($achievement === null) {
            return;
        }

        $alreadyUnlocked = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->exists();

        if ($alreadyUnlocked) {
            return;
        }

        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'unlocked_at' => now(),
        ]);

        LoyaltyPointsLedgerEntry::create([
            'user_id' => $user->id,
            'points' => $achievement->points_reward,
            'reason' => 'achievement',
            'reference_type' => Achievement::class,
            'reference_id' => $achievement->id,
            'created_at' => now(),
        ]);

        $user->notify(new AchievementUnlocked($achievement));
    }
}
