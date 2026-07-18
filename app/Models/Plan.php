<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Plano de assinatura do clube (task-3, seção 3). Auditable: mudança de
 * preço/cota de um plano em uso por assinantes ativos (task-3, seção 5).
 */
#[Fillable(['name', 'slug', 'price_cents', 'wash_quota', 'quota_period', 'rollover_quota', 'max_redemptions_per_day_per_car_wash', 'active'])]
class Plan extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'rollover_quota' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
