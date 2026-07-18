<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Estabelecimento parceiro (task-3, seção 2). Auditable: mudanças de
 * status (pending -> approved/rejected/suspended) e quem aprovou ficam
 * rastreáveis (task-3, seção 5).
 */
#[Fillable(['name', 'slug', 'document', 'phone', 'email', 'address_line', 'city', 'state', 'zip_code', 'latitude', 'longitude', 'status', 'approved_at', 'approved_by', 'rejection_reason', 'satisfaction_score'])]
class CarWash extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    /**
     * Quem administra este lava-rápido (owner/employee) — ver task-3,
     * seção 1: capacidade vem deste vínculo, não de users.role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'car_wash_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(CarWashInvitation::class);
    }

    /**
     * Produtos contratados (clube_lavagem / estacionamento) — a
     * contratação de cada um é independente (task-3, seção 2).
     */
    public function productSubscriptions(): HasMany
    {
        return $this->hasMany(CarWashProductSubscription::class);
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'satisfaction_score' => 'decimal:2',
        ];
    }
}
