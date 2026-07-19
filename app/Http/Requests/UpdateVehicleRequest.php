<?php

namespace App\Http\Requests;

use App\Rules\BrazilianLicensePlate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plate' => [
                'required',
                'string',
                new BrazilianLicensePlate,
                Rule::unique('vehicles', 'plate')->whereNull('deleted_at')->ignore($this->route('vehicle')),
            ],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.unique' => 'Esta placa já está cadastrada.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('plate')) {
            $this->merge(['plate' => strtoupper(str_replace([' ', '-'], '', $this->input('plate')))]);
        }
    }
}
