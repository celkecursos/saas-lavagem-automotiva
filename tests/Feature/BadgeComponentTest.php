<?php

use Illuminate\Support\Facades\Blade;

// Ver task-6, seção 2 — mapeamento status -> cor semântica.

test('badge resolve a cor a partir do status do dominio', function (string $status, string $variant) {
    $html = Blade::render('<x-badge :status="$status" />', ['status' => $status]);

    expect($html)->toContain('badge-'.$variant)
        ->and($html)->toContain($status);
})->with([
    ['approved', 'success'],
    ['active', 'success'],
    ['paid', 'success'],
    ['completed', 'success'],
    ['pending', 'warning'],
    ['past_due', 'warning'],
    ['rejected', 'danger'],
    ['canceled', 'danger'],
    ['chargeback', 'danger'],
    ['incomplete', 'info'],
    ['requested', 'info'],
    ['open', 'info'],
    ['suspended', 'secondary'],
    ['expired', 'secondary'],
    ['closed', 'secondary'],
]);

test('badge aceita override de variant pros casos ambiguos entre models', function () {
    // 'canceled' é danger por padrão, mas em orders é secondary (task-6).
    $html = Blade::render('<x-badge status="canceled" variant="secondary" />');

    expect($html)->toContain('badge-secondary')
        ->and($html)->not->toContain('badge-danger');
});

test('badge usa o slot como rotulo quando fornecido', function () {
    $html = Blade::render('<x-badge status="approved">Aprovado</x-badge>');

    expect($html)->toContain('Aprovado')
        ->and($html)->toContain('badge-success');
});
