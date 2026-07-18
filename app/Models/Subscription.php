<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Assinatura do usuário no clube de lavagem (task-3, seção 3). A
 * capacidade de "assinante" vem de ter uma linha aqui, não de
 * users.role (task-3, seção 1). Auditable: cancelamento, upgrade/
 * downgrade de plano (task-3, seção 5).
 */
#[Fillable(['user_id', 'plan_id', 'status', 'current_period_start', 'current_period_end', 'canceled_at', 'pending_plan_id', 'pending_renewal_discount_percent'])]
class Subscription extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'pending_plan_id');
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(SubscriptionCycle::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'payable');
    }

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
            'pending_renewal_discount_percent' => 'decimal:2',
        ];
    }
}
