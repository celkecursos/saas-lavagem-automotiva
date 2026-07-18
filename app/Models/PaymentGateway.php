<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Instância configurada de gateway; o ADM escolhe qual está ativa
 * (task-4). Auditable: troca de gateway ativo/credentials é informação
 * crítica se um pagamento falhar depois de uma troca (task-4, seção 6).
 */
#[Fillable(['payment_gateway_type_id', 'label', 'credentials', 'sandbox_mode', 'is_active'])]
class PaymentGateway extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    /**
     * Credentials nunca aparecem nos registros de auditoria.
     *
     * @var array<int, string>
     */
    protected $auditExclude = ['credentials'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PaymentGatewayType::class, 'payment_gateway_type_id');
    }

    protected function casts(): array
    {
        return [
            // Nunca texto puro no banco (task-4, seção 1).
            'credentials' => 'encrypted:array',
            'sandbox_mode' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
