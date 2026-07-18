<?php

use Illuminate\Support\Facades\Blade;

// Ver task-14, seção 6 — componentes reutilizáveis construídos antes
// das telas de CRUD que os consomem.

test('card renderiza titulo e conteudo', function () {
    $html = Blade::render('<x-card title="Resumo">Conteúdo</x-card>');

    expect($html)->toContain('Resumo')->and($html)->toContain('Conteúdo');
});

test('stat-tile renderiza valor e label', function () {
    $html = Blade::render('<x-stat-tile label="Assinantes ativos" value="42" />');

    expect($html)->toContain('42')->and($html)->toContain('Assinantes ativos');
});

test('empty-state renderiza a mensagem', function () {
    $html = Blade::render('<x-empty-state message="Nenhuma lavagem ainda" />');

    expect($html)->toContain('Nenhuma lavagem ainda');
});

test('data-table vazia mostra o empty-state embutido', function () {
    $html = Blade::render(
        '<x-data-table :rows="$rows" empty-message="Nenhum lava-rápido pendente"><x-slot:head><x-data-table.th>Nome</x-data-table.th></x-slot:head></x-data-table>',
        ['rows' => collect()],
    );

    expect($html)->toContain('Nenhum lava-rápido pendente')
        ->and($html)->not->toContain('<table');
});

test('data-table com linhas renderiza tabela e cabecalho ordenavel', function () {
    $html = Blade::render(
        '<x-data-table :rows="$rows"><x-slot:head><x-data-table.th field="name" sortable>Nome</x-data-table.th></x-slot:head><tr><td>Lava Jato A</td></tr></x-data-table>',
        ['rows' => collect([['name' => 'Lava Jato A']])],
    );

    expect($html)->toContain('<table')
        ->and($html)->toContain('Lava Jato A')
        ->and($html)->toContain('sort=name');
});

test('confirm-modal renderiza titulo, mensagem e form com csrf', function () {
    $html = Blade::render(
        '<x-confirm-modal action="/acao" title="Marcar como pago?" message="Sem volta."><x-slot:trigger><button type="button">Abrir</button></x-slot:trigger></x-confirm-modal>',
    );

    expect($html)->toContain('Marcar como pago?')
        ->and($html)->toContain('Sem volta.')
        ->and($html)->toContain('action="/acao"')
        ->and($html)->toContain('_token');
});

test('form-field renderiza label, input e usa slot quando fornecido', function () {
    // Fora do ciclo HTTP o $errors não é compartilhado pelo middleware
    // web — injeta um bag vazio pro @error do componente funcionar.
    view()->share('errors', new \Illuminate\Support\ViewErrorBag);

    $html = Blade::render('<x-form-field label="Nome" name="name" value="Cesar" />');

    expect($html)->toContain('Nome')
        ->and($html)->toContain('name="name"')
        ->and($html)->toContain('Cesar');

    $custom = Blade::render(
        '<x-form-field label="Estado" name="state"><select name="state"><option>PR</option></select></x-form-field>',
    );

    expect($custom)->toContain('<select')->and($custom)->not->toContain('<input');
});
