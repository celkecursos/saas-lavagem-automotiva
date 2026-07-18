<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autorização fina já garantida pelo middleware permission da rota.
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_gateway_type_id' => ['required', 'integer', 'exists:payment_gateway_types,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'sandbox_mode' => ['boolean'],
            // Credenciais específicas do tipo (v1: PagSeguro — token de
            // API + chave pública do encryptCard, ver task-4 seção 5.3).
            'credentials' => ['required', 'array'],
            'credentials.token' => ['required', 'string'],
            'credentials.public_key' => ['nullable', 'string'],
        ];
    }
}
