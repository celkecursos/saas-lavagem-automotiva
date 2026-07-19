<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Sessão de um veículo no estacionamento (task-3, seção 4). Auditable:
 * correção manual de entrada/saída ou valor cobrado (task-3, seção 5).
 */
#[Fillable(['parking_lot_id', 'parking_spot_id', 'parking_rate_id', 'plate', 'entry_at', 'exit_at', 'amount_charged_cents', 'payment_method', 'status'])]
class ParkingSession extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function parkingRate(): BelongsTo
    {
        return $this->belongsTo(ParkingRate::class);
    }

    protected function casts(): array
    {
        return [
            'entry_at' => 'datetime',
            'exit_at' => 'datetime',
        ];
    }
}
