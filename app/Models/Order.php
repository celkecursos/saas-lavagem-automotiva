<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Transação, agnóstica de gateway (task-4, seção 1). Auditable: todo o
 * ciclo pending -> paid/failed/refunded/chargeback (task-4, seção 6).
 */
#[Fillable(['user_id', 'payment_gateway_id', 'payable_type', 'payable_id', 'amount_cents', 'currency', 'recurring_type', 'status', 'external_reference', 'paid_at'])]
class Order extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function refundRequest(): HasOne
    {
        return $this->hasOne(OrderRefundRequest::class);
    }

    /**
     * Subscription (mensalidade do assinante) ou ParkingBillingCharge
     * (cobrança do estacionamento, task-10).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }
}
