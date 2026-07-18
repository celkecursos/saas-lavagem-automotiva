<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log bruto/imutável de webhook recebido (task-4, seção 1) — o unique
 * triplo (gateway, external_reference, event_type) garante idempotência
 * no reprocessamento.
 */
#[Fillable(['payment_gateway_id', 'event_type', 'external_reference', 'payload', 'processed_at'])]
class PaymentWebhookEvent extends Model
{
    use HasFactory;

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
