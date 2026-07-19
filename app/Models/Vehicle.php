<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Veículo do assinante — independente de qual subscription está ativa
 * no momento (task-3, seção 3; ver task-15).
 */
#[Fillable(['user_id', 'plate', 'brand', 'model', 'color', 'active'])]
class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function washRedemptions(): HasMany
    {
        return $this->hasMany(WashRedemption::class);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
