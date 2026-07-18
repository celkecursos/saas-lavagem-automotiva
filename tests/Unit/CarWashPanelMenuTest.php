<?php

use App\Support\CarWashPanelMenu;
use Illuminate\Support\Facades\Route;

// O teste de renderableItemsFor usa a facade Route — precisa do app
// bootado (TestCase do Laravel), os demais são PHP puro.
uses(Tests\TestCase::class);

// Ver task-13, seção 2.6.1 — a regra de visibilidade testada isolada
// (sem HTTP/banco): 4 combinações de produto ativo + owner/employee.

function menuLabels(array $activeProducts, string $role): array
{
    return array_column(CarWashPanelMenu::visibleItemsFor($activeProducts, $role), 'label');
}

test('nenhum produto ativo: so itens fixos', function () {
    expect(menuLabels([], 'employee'))
        ->toBe(['Dashboard', 'Meus produtos']);
});

test('so clube de lavagem ativo: itens de lavagem, sem estacionamento', function () {
    $labels = menuLabels(['clube_lavagem'], 'employee');

    expect($labels)->toContain('Confirmar lavagem', 'Lavagens', 'Repasses')
        ->and($labels)->not->toContain('Estacionamento', 'Tarifas', 'Relatório', 'Cobranças');
});

test('so estacionamento ativo: itens de estacionamento, sem lavagem', function () {
    $labels = menuLabels(['estacionamento'], 'employee');

    expect($labels)->toContain('Estacionamento', 'Tarifas', 'Relatório', 'Cobranças')
        ->and($labels)->not->toContain('Confirmar lavagem', 'Lavagens', 'Repasses');
});

test('os dois produtos ativos: todos os itens de produto', function () {
    $labels = menuLabels(['clube_lavagem', 'estacionamento'], 'employee');

    expect($labels)->toContain('Confirmar lavagem', 'Lavagens', 'Repasses')
        ->and($labels)->toContain('Estacionamento', 'Tarifas', 'Relatório', 'Cobranças');
});

test('Equipe so aparece pra owner, nunca pra employee', function () {
    expect(menuLabels([], 'owner'))->toContain('Equipe')
        ->and(menuLabels(['clube_lavagem', 'estacionamento'], 'employee'))->not->toContain('Equipe');
});

test('renderable filtra rotas ainda nao registradas sem quebrar', function () {
    // Nenhuma rota panel.* de produto existe ainda (tasks 5/8/9/10) —
    // o filtro Route::has() só deixa passar o que já está registrado,
    // sem RouteNotFoundException (proteção da task-14, topo).
    $items = CarWashPanelMenu::renderableItemsFor(['clube_lavagem', 'estacionamento'], 'owner');

    foreach ($items as $item) {
        expect(Route::has($item['route']))->toBeTrue();
    }

    expect(array_column($items, 'label'))->not->toContain('Confirmar lavagem');
});
