<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catálogo de provedores suportados pelo código (task-4, seção 1) —
 * populado por seeder, sem CRUD.
 */
#[Fillable(['slug', 'name', 'service_class', 'checkout_mode', 'requires_api_key', 'supports_webhook', 'default_endpoint'])]
class PaymentGatewayType extends Model
{
    use HasFactory;

    public function gateways(): HasMany
    {
        return $this->hasMany(PaymentGateway::class);
    }

    protected function casts(): array
    {
        return [
            'requires_api_key' => 'boolean',
            'supports_webhook' => 'boolean',
        ];
    }
}
