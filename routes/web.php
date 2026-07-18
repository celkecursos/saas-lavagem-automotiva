<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Panel\CarWashSwitchController;
use App\Http\Controllers\Panel\DashboardController as PanelDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Destino pós-login do Breeze: redireciona pro painel certo conforme o
// perfil (staff -> /admin; vínculo com lava-rápido -> /painel; área do
// assinante nasce na task-7). Sem middleware 'verified': o dono de
// lava-rápido acessa o painel "aguardando aprovação" antes de verificar
// o e-mail (task-5, seção 2).
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if (DB::table('car_wash_users')->where('user_id', $user->id)->exists()) {
        return redirect()->route('panel.dashboard');
    }

    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Checkout embedded do clube de assinatura (task-4, seção 5.3). O POST
// /planos/{plan}/assinar que processa nasce na task-7.
Route::middleware('auth')->group(function () {
    Route::get('/planos/{plan}/checkout', [CheckoutController::class, 'show'])
        ->name('plans.checkout');
});

// Painel do admin (URLs em /admin, nomes internos em inglês — ver
// convenção de idioma na orientacao.txt).
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.dashboard');

    // Gateways de pagamento (task-4, seção 4) — middleware permission
    // em cada rota individualmente (padrão do projeto, ver task-23).
    Route::get('/gateways-pagamento', [PaymentGatewayController::class, 'index'])
        ->middleware('permission:payment-gateways.index')->name('payment-gateways.index');
    Route::get('/gateways-pagamento/criar', [PaymentGatewayController::class, 'create'])
        ->middleware('permission:payment-gateways.create')->name('payment-gateways.create');
    Route::post('/gateways-pagamento', [PaymentGatewayController::class, 'store'])
        ->middleware('permission:payment-gateways.create')->name('payment-gateways.store');
    Route::get('/gateways-pagamento/{payment_gateway}/editar', [PaymentGatewayController::class, 'edit'])
        ->middleware('permission:payment-gateways.edit')->name('payment-gateways.edit');
    Route::put('/gateways-pagamento/{payment_gateway}', [PaymentGatewayController::class, 'update'])
        ->middleware('permission:payment-gateways.edit')->name('payment-gateways.update');
    // "Ativar" é ação própria, não efeito colateral do update — evita
    // ativar sem querer ao editar outra coisa (task-4, seção 4).
    Route::post('/gateways-pagamento/{payment_gateway}/ativar', [PaymentGatewayController::class, 'activate'])
        ->middleware('permission:payment-gateways.activate')->name('payment-gateways.activate');
});

// Painel do lava-rápido (URLs em /painel, ver convenção de idioma).
// SetCurrentCarWash resolve/valida o lava-rápido atual da sessão
// (task-5, seção 7).
Route::middleware(['auth', 'car-wash'])->prefix('painel')->group(function () {
    Route::get('/', PanelDashboardController::class)->name('panel.dashboard');
    Route::post('/trocar-lava-rapido', CarWashSwitchController::class)->name('panel.car-wash.switch');
});

require __DIR__.'/auth.php';
