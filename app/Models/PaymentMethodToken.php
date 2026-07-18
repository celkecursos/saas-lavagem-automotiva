<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cartão tokenizado pra renovação automática (task-3/task-4). O token
 * só vale pro gateway que o gerou.
 */
#[Fillable(['user_id', 'payment_gateway_id', 'token', 'brand', 'last_four'])]
class PaymentMethodToken extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
        ];
    }
}
