<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rastreia cada indicação e o status do bônus (task-16, seção 1). NÃO é
 * Auditable — mecanismo automático entre usuários, não ação de staff.
 */
#[Fillable(['referrer_user_id', 'referred_user_id', 'status', 'qualified_at', 'granted_subscription_cycle_id', 'granted_at'])]
class ReferralReward extends Model
{
    use HasFactory;

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function grantedSubscriptionCycle(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCycle::class, 'granted_subscription_cycle_id');
    }

    protected function casts(): array
    {
        return [
            'qualified_at' => 'datetime',
            'granted_at' => 'datetime',
        ];
    }
}
