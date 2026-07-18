<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Contratação de cada produto pelo lava-rápido (task-3, seção 2).
 * Auditable: ativação/suspensão/cancelamento ficam rastreáveis
 * (task-3, seção 5).
 */
#[Fillable(['car_wash_id', 'product', 'status', 'activated_at', 'suspended_at', 'approved_by', 'payout_plan_id'])]
class CarWashProductSubscription extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    public function payoutPlan(): BelongsTo
    {
        return $this->belongsTo(PayoutPlan::class);
    }

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }
}
