<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Regra de visibilidade dos itens da sidebar do painel do lava-rápido
 * (task-14, seção 3). Extraída pra classe própria (e não @if solto no
 * Blade) pra ser testável como unit test, sem HTTP/banco — ver task-13,
 * seção 2.6.1.
 *
 * Nomes de rota panel.* ainda não registrados (tasks 5/8/9/10) são
 * filtrados por Route::has() só na hora de renderizar
 * (renderableItemsFor) — a REGRA de produto/papel (visibleItemsFor)
 * independe disso.
 */
class CarWashPanelMenu
{
    /**
     * Itens visíveis conforme produtos ativos e papel do usuário no
     * car_wash atual — a regra pura, sem filtro de rota.
     *
     * @param  array<int, string>  $activeProducts  ex: ['clube_lavagem', 'estacionamento']
     * @param  string  $roleInCarWash  'owner' ou 'employee'
     * @return array<int, array{label: string, route: string}>
     */
    public static function visibleItemsFor(array $activeProducts, string $roleInCarWash): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'panel.dashboard'],
            ['label' => 'Meus produtos', 'route' => 'panel.products.index'],
        ];

        if (in_array('clube_lavagem', $activeProducts, true)) {
            $items[] = ['label' => 'Confirmar lavagem', 'route' => 'panel.washes.confirm'];
            $items[] = ['label' => 'Lavagens', 'route' => 'panel.washes.index'];
            $items[] = ['label' => 'Repasses', 'route' => 'panel.payouts.index'];
        }

        if (in_array('estacionamento', $activeProducts, true)) {
            $items[] = ['label' => 'Estacionamento', 'route' => 'panel.parking.sessions.index'];
            $items[] = ['label' => 'Tarifas', 'route' => 'panel.parking.rates.index'];
            $items[] = ['label' => 'Relatório', 'route' => 'panel.parking.report'];
            $items[] = ['label' => 'Cobranças', 'route' => 'panel.parking.charges.index'];
        }

        // Um 'employee' não convida gente — Equipe é só pra 'owner'.
        if ($roleInCarWash === 'owner') {
            $items[] = ['label' => 'Equipe', 'route' => 'panel.team.index'];
        }

        return $items;
    }

    /**
     * Itens prontos pra view: aplica o Route::has() defensivo por cima
     * da regra acima (rotas nascem aos poucos nas tasks seguintes).
     *
     * @param  array<int, string>  $activeProducts
     * @return array<int, array{label: string, route: string}>
     */
    public static function renderableItemsFor(array $activeProducts, string $roleInCarWash): array
    {
        return array_values(array_filter(
            static::visibleItemsFor($activeProducts, $roleInCarWash),
            fn (array $item): bool => Route::has($item['route']),
        ));
    }
}
