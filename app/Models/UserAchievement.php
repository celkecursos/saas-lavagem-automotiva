<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Conquista desbloqueada por um assinante (task-20, seção 1) — unique
 * (user_id, achievement_id), nunca duplica.
 */
#[Fillable(['user_id', 'achievement_id', 'unlocked_at'])]
class UserAchievement extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    protected function casts(): array
    {
        return [
            'unlocked_at' => 'datetime',
        ];
    }
}
