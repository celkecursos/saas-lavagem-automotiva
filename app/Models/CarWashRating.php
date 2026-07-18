<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Avaliação do assinante sobre o lava-rápido — alimenta
 * car_washes.satisfaction_score (task-3, seção 3; ver task-8/9).
 */
#[Fillable(['wash_redemption_id', 'car_wash_id', 'user_id', 'score', 'comment'])]
class CarWashRating extends Model
{
    use HasFactory;

    public function washRedemption(): BelongsTo
    {
        return $this->belongsTo(WashRedemption::class);
    }

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }
}
