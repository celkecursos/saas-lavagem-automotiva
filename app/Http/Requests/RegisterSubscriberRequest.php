<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Cadastro self-service do assinante (task-7, seção 1).
 */
class RegisterSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            // CPF opcional na v1 — só obrigatório se decidir emitir nota
            // fiscal por CPF; não bloquear cadastro por causa disso agora.
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Opcional — código inválido não bloqueia o resto do
            // cadastro (task-16, seção 2, passo 2).
            'referral_code' => ['nullable', 'string', 'max:8'],
        ];
    }
}
