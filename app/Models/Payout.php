<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Lote de repasse financeiro a um lava-rápido (task-3, seção 3;
 * cálculo na task-9). Auditable: todo o ciclo de payouts (task-3,
 * seção 5).
 */
#[Fillable(['car_wash_id', 'period_start', 'period_end', 'total_amount_cents', 'status', 'paid_at'])]
class Payout extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayoutItem::class);
    }

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
