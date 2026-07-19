<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * 1 registro por lava-rápido por período — mesmo raciocínio de "lote"
 * que payouts, na direção contrária (task-3, seção 4.1). Auditable:
 * revisão manual de cobrança sinalizada pelo antifraude (task-3, §5).
 */
#[Fillable(['car_wash_id', 'period_start', 'period_end', 'wash_count', 'total_spots_snapshot', 'parking_sessions_count', 'is_free', 'fee_percentage_applied', 'fee_amount_cents', 'order_id', 'flagged_for_review', 'status'])]
class ParkingBillingCharge extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'is_free' => 'boolean',
            'flagged_for_review' => 'boolean',
        ];
    }
}
