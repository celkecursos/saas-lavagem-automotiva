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
            // Única credencial necessária (v1: PagSeguro) é o Token de
            // API — a chave pública do encryptCard é obtida via API com
            // esse mesmo token, não é cadastrada (task-4, seção 5.3).
            'credentials' => ['required', 'array'],
            'credentials.token' => ['required', 'string'],
        ];
    }
}
