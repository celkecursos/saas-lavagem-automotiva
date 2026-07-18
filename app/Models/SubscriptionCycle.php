<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Um registro por ciclo de cobrança/uso — controla quanto da cota já
 * foi consumida (task-3, seção 3).
 */
#[Fillable(['subscription_id', 'period_start', 'period_end', 'quota_total', 'quota_used'])]
class SubscriptionCycle extends Model
{
    use HasFactory;

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function washRedemptions(): HasMany
    {
        return $this->hasMany(WashRedemption::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }
}
