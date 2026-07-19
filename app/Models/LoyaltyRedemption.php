<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Catálogo de recompensas trocáveis por pontos (task-20, seção 1).
 * Auditable: mudar points_cost afeta quanto os usuários pagam por
 * uma recompensa — mesmo critério de payout_plans (task-3, seção 5).
 */
#[Fillable(['name', 'points_cost', 'reward_type', 'discount_percent', 'active'])]
class LoyaltyRedemption extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'active' => 'boolean',
        ];
    }
}
