<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vantagem de marketing da vitrine de planos — puramente descritivo,
 * NÃO é Auditable de propósito (task-3, seção 3; task-13, seção 2.7).
 */
#[Fillable(['plan_id', 'label', 'sort_order', 'active'])]
class PlanFeature extends Model
{
    use HasFactory;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
