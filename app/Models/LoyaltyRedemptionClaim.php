<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de cada resgate feito por um usuário (task-20, seção 1) —
 * points_spent congelado no momento, não muda se o catálogo mudar
 * depois.
 */
#[Fillable(['user_id', 'loyalty_redemption_id', 'points_spent', 'applied_at'])]
class LoyaltyRedemptionClaim extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loyaltyRedemption(): BelongsTo
    {
        return $this->belongsTo(LoyaltyRedemption::class);
    }

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
        ];
    }
}
