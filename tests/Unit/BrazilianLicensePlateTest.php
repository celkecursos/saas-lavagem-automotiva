<?php

use App\Rules\BrazilianLicensePlate;
use Illuminate\Support\Facades\Validator;

// Ver task-15, seção 2, e task-13, seção 2.4 — tabela de casos válidos/
// inválidos rodando direto contra a Rule, sem HTTP nem banco.
// Precisa do app bootado só pra facade Validator, não de banco.
uses(Tests\TestCase::class);

function validatePlate(string $plate): bool
{
    return Validator::make(['plate' => $plate], ['plate' => [new BrazilianLicensePlate]])->passes();
}

test('formatos validos', function (string $plate) {
    expect(validatePlate($plate))->toBeTrue();
})->with([
    'antigo maiusculo' => ['ABC1234'],
    'antigo minusculo' => ['abc1234'],
    'antigo com espaco' => ['ABC 1234'],
    'antigo com traco' => ['ABC-1234'],
    'mercosul maiusculo' => ['ABC1D23'],
    'mercosul minusculo' => ['abc1d23'],
    'mercosul com espaco' => ['ABC 1D23'],
]);

test('formatos invalidos', function (string $plate) {
    expect(validatePlate($plate))->toBeFalse();
})->with([
    // String vazia não é testada aqui de propósito: por padrão, uma
    // Rule não-implícita do Laravel não roda pra valor vazio — quem
    // barra campo vazio é a regra 'required' no FormRequest (já
    // coberta pelos testes de Feature do CRUD).
    'curta demais' => ['ABC123'],
    'longa demais' => ['ABC12345'],
    'so numeros' => ['1234567'],
    'so letras' => ['ABCDEFG'],
    'caracteres especiais' => ['AB#1234'],
    'formato antigo com letra a mais no meio' => ['ABCD234'],
    'mercosul com letra na posicao errada' => ['ABCD123'],
]);
