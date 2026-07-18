<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'cpf', 'referred_by_user_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
