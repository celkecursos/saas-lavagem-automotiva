<?php

use Illuminate\Support\Facades\Blade;

// Ver task-13, seção 2.11 — o comportamento é 100% client-side
// (localStorage + Alpine); aqui só confirmamos que o botão está
// presente no HTML renderizado dos layouts.

test('componente theme-toggle renderiza com a logica de localStorage', function () {
    $html = Blade::render('<x-theme-toggle />');

    expect($html)->toContain('Alternar tema claro/escuro')
        ->and($html)->toContain("localStorage.getItem('theme')")
        ->and($html)->toContain('prefers-color-scheme');
});

test('botao de tema presente nos 3 layouts', function (string $layout) {
    expect(view('layouts.'.$layout)->render())
        ->toContain('Alternar tema claro/escuro');
})->with(['admin', 'car-wash-panel', 'public']);
