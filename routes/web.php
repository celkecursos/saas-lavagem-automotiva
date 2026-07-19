<?php

use App\Http\Controllers\Admin\CarWashController;
use App\Http\Controllers\Admin\CancellationRequestController;
use App\Http\Controllers\Admin\CarWashProductSubscriptionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Panel\CarWashSwitchController;
use App\Http\Controllers\Panel\DashboardController as PanelDashboardController;
use App\Http\Controllers\Panel\PayoutController as PanelPayoutController;
use App\Http\Controllers\Panel\ProductController as PanelProductController;
use App\Http\Controllers\Panel\RegistrationController as PanelRegistrationController;
use App\Http\Controllers\Panel\TeamController as PanelTeamController;
use App\Http\Controllers\Panel\WashConfirmationController;
use App\Http\Controllers\Panel\WashHistoryController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RegisterCarWashController;
use App\Http\Controllers\RegisterSubscriberController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WashController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Cadastro self-service do lava-rápido (task-5, seção 2).
Route::middleware('guest')->group(function () {
    Route::get('/parceiros/cadastro', [RegisterCarWashController::class, 'create'])
        ->name('partners.register');
    Route::post('/parceiros/cadastro', [RegisterCarWashController::class, 'store'])
        ->name('partners.register.store');
});

// Cadastro self-service do assinante (task-7, seção 1) — substitui o
// /register genérico do Breeze; nome de rota 'register' mantido porque
// as views do Breeze (welcome, etc.) já referenciam route('register').
Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisterSubscriberController::class, 'create'])
        ->name('register');
    Route::post('/registro', [RegisterSubscriberController::class, 'store'])
        ->name('register.store');
});

// Vitrine de planos (task-7, seção 2).
Route::get('/planos', [PlanController::class, 'index'])->name('plans.index');

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

    if ($user->subscriptions()->exists()) {
        return redirect()->route('subscription.show');
    }

    return redirect()->route('plans.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Checkout embedded do clube de assinatura (task-4, seção 5.3; POST
// que processa é da task-7, seção 3).
Route::middleware('auth')->group(function () {
    Route::get('/planos/{plan}/checkout', [CheckoutController::class, 'show'])
        ->name('plans.checkout');
    Route::post('/planos/{plan}/assinar', [CheckoutController::class, 'store'])
        ->name('plans.subscribe');

    // Painel do assinante (task-7, seções 5 e 6).
    Route::get('/assinatura', [SubscriptionController::class, 'show'])
        ->name('subscription.show');
    Route::post('/assinatura/cancelar', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    Route::post('/assinatura/trocar-plano', [SubscriptionController::class, 'changePlan'])
        ->name('subscription.change-plan');

    // Minhas indicações (task-16, seção 3).
    Route::get('/indicacoes', [ReferralController::class, 'index'])->name('referrals.index');

    // Meus veículos (task-15, seção 2).
    Route::get('/veiculos', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/veiculos/criar', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/veiculos', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/veiculos/{vehicle}/editar', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/veiculos/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/veiculos/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::get('/veiculos/{vehicle}/lavagens', [VehicleController::class, 'washes'])->name('vehicles.washes');

    // Resgate de lavagem — lado do assinante (task-8, seção 6).
    Route::get('/lavagem/escolher', [WashController::class, 'choose'])->name('wash.choose');
    Route::post('/lavagem/{car_wash}/resgatar', [WashController::class, 'request'])->name('wash.request');
    Route::post('/lavagem/{wash_redemption}/cancelar', [WashController::class, 'cancel'])->name('wash.cancel');
    Route::post('/lavagem/{wash_redemption}/avaliar', [WashController::class, 'rate'])->name('wash.rate');
    Route::post('/lavagem/{wash_redemption}/solicitar-cancelamento', [WashController::class, 'requestCancellation'])
        ->name('wash.request-cancellation');
});

// Webhook de pagamento — genérico por gateway (task-4/task-7, seção 3).
Route::post('/webhooks/{gatewayTypeSlug}', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook');

// Painel do admin (URLs em /admin, nomes internos em inglês — ver
// convenção de idioma na orientacao.txt).
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.dashboard');

    // Fila de aprovação de lava-rápidos (task-5, seção 4).
    Route::get('/lava-rapidos', [CarWashController::class, 'index'])
        ->middleware('permission:car-washes.index')->name('car-washes.index');
    Route::get('/lava-rapidos/{car_wash}', [CarWashController::class, 'show'])
        ->middleware('permission:car-washes.index')->name('car-washes.show');
    Route::post('/lava-rapidos/{car_wash}/aprovar', [CarWashController::class, 'approve'])
        ->middleware('permission:car-washes.approve')->name('car-washes.approve');
    Route::post('/lava-rapidos/{car_wash}/rejeitar', [CarWashController::class, 'reject'])
        ->middleware('permission:car-washes.reject')->name('car-washes.reject');
    Route::post('/lava-rapidos/{car_wash}/suspender', [CarWashController::class, 'suspend'])
        ->middleware('permission:car-washes.suspend')->name('car-washes.suspend');

    // Fila de ativação do clube de lavagem (task-5, seção 5).
    Route::get('/ativacoes-clube', [CarWashProductSubscriptionController::class, 'index'])
        ->middleware('permission:car-wash-product-subscriptions.index')
        ->name('car-wash-product-subscriptions.index');
    Route::post('/ativacoes-clube/{subscription}/aprovar', [CarWashProductSubscriptionController::class, 'approve'])
        ->middleware('permission:car-wash-product-subscriptions.approve')
        ->name('car-wash-product-subscriptions.approve');
    Route::post('/ativacoes-clube/{subscription}/rejeitar', [CarWashProductSubscriptionController::class, 'reject'])
        ->middleware('permission:car-wash-product-subscriptions.reject')
        ->name('car-wash-product-subscriptions.reject');

    // Repasses (task-9, seção 3).
    Route::get('/repasses', [PayoutController::class, 'index'])
        ->middleware('permission:payouts.index')->name('payouts.index');
    Route::get('/repasses/{payout}', [PayoutController::class, 'show'])
        ->middleware('permission:payouts.index')->name('payouts.show');
    Route::post('/repasses/{payout}/marcar-pago', [PayoutController::class, 'markPaid'])
        ->middleware('permission:payouts.mark-paid')->name('payouts.mark-paid');
    Route::post('/repasses/{payout}/marcar-falhou', [PayoutController::class, 'markFailed'])
        ->middleware('permission:payouts.mark-failed')->name('payouts.mark-failed');

    // Solicitações de cancelamento (task-9, seção 3.2).
    Route::get('/cancelamentos', [CancellationRequestController::class, 'index'])
        ->middleware('permission:cancellation-requests.index')->name('cancellation-requests.index');
    Route::post('/cancelamentos/{cancellation_request}/aprovar', [CancellationRequestController::class, 'approve'])
        ->middleware('permission:cancellation-requests.approve')->name('cancellation-requests.approve');
    Route::post('/cancelamentos/{cancellation_request}/rejeitar', [CancellationRequestController::class, 'reject'])
        ->middleware('permission:cancellation-requests.reject')->name('cancellation-requests.reject');

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

    // Correção/reenvio de cadastro rejeitado (task-5, seção 3).
    Route::get('/cadastro/corrigir', [PanelRegistrationController::class, 'edit'])
        ->name('panel.registration.edit');
    Route::put('/cadastro', [PanelRegistrationController::class, 'update'])
        ->name('panel.registration.update');

    // Meus produtos (task-5, seção 5).
    Route::get('/produtos', [PanelProductController::class, 'index'])
        ->name('panel.products.index');
    Route::post('/produtos/estacionamento/ativar', [PanelProductController::class, 'activateParking'])
        ->name('panel.products.parking.activate');
    Route::post('/produtos/estacionamento/pausar', [PanelProductController::class, 'pauseParking'])
        ->name('panel.products.parking.pause');
    Route::post('/produtos/clube-lavagem/solicitar', [PanelProductController::class, 'requestClub'])
        ->name('panel.products.club.request');
    Route::post('/produtos/clube-lavagem/pausar', [PanelProductController::class, 'pauseClub'])
        ->name('panel.products.club.pause');

    // Equipe (task-5, seção 6) — owner convida, employee não.
    Route::get('/equipe', [PanelTeamController::class, 'index'])->name('panel.team.index');
    Route::post('/equipe/convidar', [PanelTeamController::class, 'invite'])->name('panel.team.invite');

    // Confirmar lavagem (task-8, seção 4).
    Route::get('/confirmar-lavagem', [WashConfirmationController::class, 'show'])
        ->name('panel.washes.confirm');
    Route::post('/confirmar-lavagem', [WashConfirmationController::class, 'confirm'])
        ->name('panel.washes.confirm.store');
    Route::get('/lavagens', [WashHistoryController::class, 'index'])->name('panel.washes.index');
    Route::post('/lavagens/{wash_redemption}/solicitar-cancelamento', [WashHistoryController::class, 'requestCancellation'])
        ->name('panel.washes.request-cancellation');

    // Repasses recebidos (task-9, seção 4).
    Route::get('/repasses', [PanelPayoutController::class, 'index'])->name('panel.payouts.index');
    Route::get('/repasses/{payout}', [PanelPayoutController::class, 'show'])->name('panel.payouts.show');
});

// Aceite público de convite de equipe (task-5, seção 6).
Route::get('/convites/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/convites/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

require __DIR__.'/auth.php';
