<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CRUD de planos (task-11, seção 4).
 */
class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'wash_quota' => ['required', 'integer', 'min:1'],
            'quota_period' => ['required', Rule::in(['monthly', 'weekly', 'yearly'])],
            'rollover_quota' => ['boolean'],
            'max_redemptions_per_day_per_car_wash' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }
}
