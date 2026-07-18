<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Cadastro self-service do lava-rápido (task-5, seção 2): 1 form com
 * dados do responsável (users) + do estabelecimento (car_washes).
 */
class RegisterCarWashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Responsável (vira users + owner em car_wash_users).
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Estabelecimento (vira car_washes).
            'car_wash_name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:18', 'unique:car_washes,document'],
            'car_wash_phone' => ['nullable', 'string', 'max:20'],
            'car_wash_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:car_washes,email'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:9'],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_email.unique' => 'Já existe um cadastro com este e-mail.',
            'document.unique' => 'Já existe um lava-rápido cadastrado com este CNPJ/CPF.',
            'car_wash_email.unique' => 'Já existe um lava-rápido cadastrado com este e-mail.',
        ];
    }
}
