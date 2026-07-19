<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * O estacionamento em si (task-3, seção 4; task-10). V1 controla
 * ocupação só por contagem (total_spots - sessões abertas), sem mapear
 * vaga individual (parking_spots adiado pra v2).
 */
#[Fillable(['car_wash_id', 'name', 'total_spots'])]
class ParkingLot extends Model
{
    use HasFactory;

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ParkingRate::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }
}
