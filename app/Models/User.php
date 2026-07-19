<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Auditable: suspender/reativar uma conta é ação sensível o bastante
 * pra rastrear quem fez e quando (task-3, seção 5; task-22, seção 1).
 */
#[Fillable(['name', 'email', 'password', 'phone', 'cpf', 'referred_by_user_id', 'suspended_at', 'suspension_reason'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements Auditable, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;
    use \OwenIt\Auditing\Auditable;

    protected static function booted(): void
    {
        // Código de indicação gerado na criação de QUALQUER user —
        // 8 alfanuméricos maiúsculos, com retry em caso de colisão
        // (task-16, seção 2, passo 1).
        static::creating(function (User $user) {
            if (blank($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    private static function generateUniqueReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * A capacidade de "assinante" vem de ter uma linha aqui (mesmo que
     * 'canceled' — histórico ainda é visível), nunca de users.role
     * (task-3, seção 1).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    /**
     * Indicações que este usuário FEZ (ele é o referrer) — task-16.
     */
    public function referralsMade(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'referrer_user_id');
    }

    /**
     * Indicação que TROUXE este usuário (ele é o indicado) — task-16.
     */
    public function referralReceived(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'referred_user_id');
    }

    /**
     * Pedidos deste usuário — cobre tanto mensalidade de clube quanto,
     * se ele for dono de lava-rápido, cobranças de estacionamento
     * pagas por ele (orders.user_id já é genérico o bastante — task-22,
     * seção 3).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carWashRatings(): HasMany
    {
        return $this->hasMany(CarWashRating::class);
    }

    /**
     * Lava-rápidos onde é owner/employee (task-5, seção 1; task-22,
     * seção 3).
     */
    public function carWashes(): BelongsToMany
    {
        return $this->belongsToMany(CarWash::class, 'car_wash_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function cancellationRequestsMade(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'requested_by_user_id');
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function loyaltyLedgerEntries(): HasMany
    {
        return $this->hasMany(LoyaltyPointsLedgerEntry::class);
    }

    public function loyaltyRedemptionClaims(): HasMany
    {
        return $this->hasMany(LoyaltyRedemptionClaim::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
