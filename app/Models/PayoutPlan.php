<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Catálogo de planos de repasse mantido pelo admin (task-3, seção 3).
 * Auditable: mudar um valor afeta o cálculo de todos os lava-rápidos
 * que escolheram aquele plano (task-3, seção 5).
 */
#[Fillable(['category', 'level', 'label', 'base_price_cents', 'active'])]
class PayoutPlan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
