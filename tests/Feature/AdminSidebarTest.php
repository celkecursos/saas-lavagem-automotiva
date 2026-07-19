<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

// Ver task-13, seção 2.6.1 — sidebar do admin só mostra cada item se o
// admin tiver a permission correspondente, e itens de rota ainda não
// registrada não aparecem nem derrubam a página.

function renderAdminSidebar(): string
{
    return view('layouts.partials.admin-sidebar')->render();
}

// Rotas nomeadas registradas em runtime (dentro do teste) só ficam
// visíveis pro Route::has() depois de recompilar o índice de nomes.
function refreshRouteNames(): void
{
    Route::getRoutes()->refreshNameLookups();
}

test('item de rota nao registrada nao aparece e nao quebra a sidebar', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);

    // Rotas já registradas aparecem; as de tasks ainda não implementadas
    // (ex: parking-billing-*, task-10) não aparecem e NÃO derrubam a
    // página com RouteNotFoundException — proteção Route::has() da
    // task-14.
    $html = renderAdminSidebar();

    expect($html)->toContain('Lava-rápidos')
        ->and($html)->toContain('Auditoria')
        ->and($html)->not->toContain('Configurações do estacionamento');
});

test('item aparece so pra quem tem a permission correspondente', function () {
    $this->seed(DatabaseSeeder::class);
    Route::get('/admin/lava-rapidos-teste', fn () => 'ok')->name('car-washes.index');
    Route::get('/admin/roles-teste', fn () => 'ok')->name('roles.index');
    refreshRouteNames();

    $administrador = User::factory()->create();
    $administrador->assignRole('Administrador');
    $this->actingAs($administrador);
    $html = renderAdminSidebar();

    // Administrador tem car-washes.index, mas roles.* é só do Super Admin.
    expect($html)->toContain('Lava-rápidos')
        ->and($html)->not->toContain('roles-teste');

    $semPermissao = User::factory()->create();
    $this->actingAs($semPermissao);

    expect(renderAdminSidebar())->not->toContain('Lava-rápidos');
});

test('badge de pendencia mostra o contador quando ha registros pending', function () {
    $this->seed(DatabaseSeeder::class);
    Route::get('/admin/ativacoes-teste', fn () => 'ok')
        ->name('car-wash-product-subscriptions.index');
    refreshRouteNames();

    $carWashId = DB::table('car_washes')->insertGetId([
        'name' => 'Lava Jato Teste',
        'slug' => 'lava-jato-teste',
        'document' => '12345678000199',
        'email' => 'teste@exemplo.com',
        'address_line' => 'Rua 1',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zip_code' => '80000000',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('car_wash_product_subscriptions')->insert([
        'car_wash_id' => $carWashId,
        'product' => 'clube_lavagem',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $this->actingAs($user);

    expect(renderAdminSidebar())->toContain('Ativação do clube de lavagem')
        ->and(renderAdminSidebar())->toContain('badge-warning');
});
