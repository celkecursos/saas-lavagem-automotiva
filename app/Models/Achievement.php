<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de conquistas (task-20, seção 1). `code` é a chave que o
 * AchievementChecker usa pra saber qual regra checar — não é 100%
 * dado-driven.
 */
#[Fillable(['code', 'name', 'description', 'icon', 'points_reward', 'active'])]
class Achievement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
