<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'sandbox_mode' => ['boolean'],
            // Token em branco no update = mantém o atual (o campo nunca é
            // pré-preenchido na tela, por ser segredo).
            'credentials' => ['nullable', 'array'],
            'credentials.token' => ['nullable', 'string'],
            'credentials.public_key' => ['nullable', 'string'],
        ];
    }
}
