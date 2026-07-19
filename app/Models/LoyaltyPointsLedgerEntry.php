<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Livro-razão de pontos de fidelidade (task-20, seção 1) — saldo é
 * sempre SUM(points), nunca um campo de cache.
 */
#[Fillable(['user_id', 'points', 'reason', 'reference_type', 'reference_id'])]
class LoyaltyPointsLedgerEntry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'loyalty_points_ledger';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public static function balanceFor(int $userId): int
    {
        return (int) static::where('user_id', $userId)->sum('points');
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
