<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Catálogo de recompensas trocáveis por pontos (task-20, seção 1).
 * discount_percent só faz sentido (e é exigido) quando
 * reward_type='discount_next_renewal'.
 */
class StoreLoyaltyRedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'reward_type' => ['required', 'in:free_wash,discount_next_renewal'],
            'discount_percent' => ['required_if:reward_type,discount_next_renewal', 'nullable', 'numeric', 'min:0.01', 'max:100'],
            'active' => ['boolean'],
        ];
    }
}
