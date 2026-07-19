<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Lavagem resgatada por um assinante em um lava-rápido (task-3,
 * seção 3; fluxo do código na task-8). Auditable: cancelamento de uma
 * lavagem já resgatada (task-3, seção 5).
 */
#[Fillable(['subscription_cycle_id', 'car_wash_id', 'redeemed_at', 'confirmation_code', 'code_expires_at', 'confirmed_by_user_id', 'vehicle_id', 'status', 'base_price_cents_snapshot', 'payout_item_id'])]
class WashRedemption extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function subscriptionCycle(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCycle::class);
    }

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function payoutItem(): BelongsTo
    {
        return $this->belongsTo(PayoutItem::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(CarWashRating::class);
    }

    protected function casts(): array
    {
        return [
            'redeemed_at' => 'datetime',
            'code_expires_at' => 'datetime',
        ];
    }
}
