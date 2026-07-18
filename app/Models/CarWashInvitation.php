<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Convite de equipe do lava-rápido (task-3, seção 1; fluxo na task-5,
 * seção 6).
 */
#[Fillable(['car_wash_id', 'email', 'token', 'expires_at', 'accepted_at'])]
class CarWashInvitation extends Model
{
    use HasFactory;

    public function carWash(): BelongsTo
    {
        return $this->belongsTo(CarWash::class);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }
}
