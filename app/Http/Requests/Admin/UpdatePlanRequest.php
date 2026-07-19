<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'wash_quota' => ['required', 'integer', 'min:1'],
            'quota_period' => ['required', Rule::in(['monthly', 'weekly', 'yearly'])],
            'rollover_quota' => ['boolean'],
            'max_redemptions_per_day_per_car_wash' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
        ];
    }
}
