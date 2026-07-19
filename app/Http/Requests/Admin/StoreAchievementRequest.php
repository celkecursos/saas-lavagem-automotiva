<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Catálogo de conquistas (task-20, seção 1). `code` não é 100%
 * dado-driven — precisa de uma checagem correspondente no
 * AchievementChecker pra desbloquear sozinho.
 */
class StoreAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $achievementId = $this->route('achievement')?->id;

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('achievements', 'code')->ignore($achievementId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:255'],
            'points_reward' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
        ];
    }
}
