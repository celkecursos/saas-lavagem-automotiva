<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Solicitação de reembolso de um order (task-21, seção 1) — pedir já é
 * a decisão (self-service e admin auto-aprovam na criação, sem
 * 'pending'); só falta processar de fato no gateway. Auditable:
 * reembolso é dinheiro saindo, sempre rastreável.
 */
#[Fillable(['order_id', 'requested_by_user_id', 'initiated_by', 'reason', 'status', 'requested_at', 'processed_at'])]
class OrderRefundRequest extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }
}
