<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Item de um lote de repasse — 1 item = 1 lavagem confirmada (task-3,
 * seção 3). Auditable junto do payout (task-3, seção 5).
 */
#[Fillable(['payout_id', 'wash_redemption_id', 'amount_cents'])]
class PayoutItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    public function washRedemption(): BelongsTo
    {
        return $this->belongsTo(WashRedemption::class);
    }
}
