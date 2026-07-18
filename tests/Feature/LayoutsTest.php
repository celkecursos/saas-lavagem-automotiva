<?php

// Ver task-14, seção 1 — três layouts distintos compartilhando a
// paleta da task-6, favicon e logo.

test('layouts base renderizam sem erro e incluem favicon e logo', function (string $layout) {
    $html = view('layouts.'.$layout)->render();

    expect($html)->toContain('favicon.ico')
        ->and($html)->toContain('images/logo.png');
})->with(['admin', 'car-wash-panel', 'public']);
