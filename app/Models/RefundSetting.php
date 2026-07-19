<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Singleton — janela estendida de reembolso self-service depois dos 7
 * dias fixos do CDC art. 49 (task-21, seção 1). Auditable: muda a janela
 * de reembolso pra todo mundo.
 */
#[Fillable(['extended_self_service_enabled', 'extended_self_service_until_days'])]
class RefundSetting extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'extended_self_service_enabled' => false,
            'extended_self_service_until_days' => null,
            'updated_at' => now(),
        ]);
    }

    protected function casts(): array
    {
        return [
            'extended_self_service_enabled' => 'boolean',
            'updated_at' => 'datetime',
        ];
    }
}
