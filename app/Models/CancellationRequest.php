<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Solicitação de cancelamento de uma lavagem/sessão JÁ confirmada
 * (task-3, seção 3; fluxo na task-8, seção 2, passo 8). Fica pending
 * até o admin decidir — nada muda no registro original até lá.
 */
#[Fillable(['requestable_type', 'requestable_id', 'requested_by_user_id', 'reason', 'status', 'resolved_by_user_id', 'resolved_at'])]
class CancellationRequest extends Model
{
    use HasFactory;

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }
}
