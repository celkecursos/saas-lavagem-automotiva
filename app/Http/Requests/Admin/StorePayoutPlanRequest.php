<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Catálogo de repasse (task-3, seção 3; task-9, seção 1) — o
 * lava-rápido ESCOLHE um item daqui, nunca digita valor livre.
 */
class StorePayoutPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1'],
            'label' => ['required', 'string', 'max:255'],
            'base_price_cents' => ['required', 'integer', 'min:0'],
            'active' => ['boolean'],
        ];
    }
}
