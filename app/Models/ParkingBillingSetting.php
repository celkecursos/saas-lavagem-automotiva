<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Singleton de configuração da monetização do estacionamento (task-3,
 * seção 4.1). Auditable: muda o percentual cobrado de todos os
 * lava-rápidos (task-3, seção 5).
 */
#[Fillable(['fee_percentage', 'max_turns_per_day_per_spot'])]
class ParkingBillingSetting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'fee_percentage' => 10.00,
            'max_turns_per_day_per_spot' => 6,
            'updated_at' => now(),
        ]);
    }

    protected function casts(): array
    {
        return [
            'fee_percentage' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }
}
