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
     * @return array<int, array{label: string, route: string, icon: string}>
     */
    public static function visibleItemsFor(array $activeProducts, string $roleInCarWash): array
    {
        $items = [
            ['label' => 'Dashboard', 'route' => 'panel.dashboard', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
            ['label' => 'Meus produtos', 'route' => 'panel.products.index', 'icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
        ];

        if (in_array('clube_lavagem', $activeProducts, true)) {
            $items[] = ['label' => 'Confirmar lavagem', 'route' => 'panel.washes.confirm', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
            $items[] = ['label' => 'Lavagens', 'route' => 'panel.washes.index', 'icon' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z'];
            $items[] = ['label' => 'Repasses', 'route' => 'panel.payouts.index', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
        }

        if (in_array('estacionamento', $activeProducts, true)) {
            $items[] = ['label' => 'Estacionamento', 'route' => 'panel.parking.sessions.index', 'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'];
            $items[] = ['label' => 'Tarifas', 'route' => 'panel.parking.rates.index', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z'];
            $items[] = ['label' => 'Relatório', 'route' => 'panel.parking.report', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'];
            $items[] = ['label' => 'Cobranças', 'route' => 'panel.parking.charges.index', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'];
        }

        // Um 'employee' não convida gente — Equipe é só pra 'owner'.
        if ($roleInCarWash === 'owner') {
            $items[] = ['label' => 'Equipe', 'route' => 'panel.team.index', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'];
        }

        return $items;
    }

    /**
     * Itens prontos pra view: aplica o Route::has() defensivo por cima
     * da regra acima (rotas nascem aos poucos nas tasks seguintes).
     *
     * @param  array<int, string>  $activeProducts
     * @return array<int, array{label: string, route: string, icon: string}>
     */
    public static function renderableItemsFor(array $activeProducts, string $roleInCarWash): array
    {
        return array_values(array_filter(
            static::visibleItemsFor($activeProducts, $roleInCarWash),
            fn (array $item): bool => Route::has($item['route']),
        ));
    }
}
