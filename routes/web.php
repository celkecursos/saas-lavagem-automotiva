<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CarWashController;
use App\Http\Controllers\Admin\CancellationRequestController;
use App\Http\Controllers\Admin\CarWashProductSubscriptionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\PayoutPlanController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PlanFeatureController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Panel\CarWashSwitchController;
use App\Http\Controllers\Panel\DashboardController as PanelDashboardController;
use App\Http\Controllers\Panel\ParkingEntryController;
use App\Http\Controllers\Admin\ParkingBillingChargeController as AdminParkingBillingChargeController;
use App\Http\Controllers\Admin\ParkingBillingSettingController;
use App\Http\Controllers\Panel\ParkingBillingChargeController;
use App\Http\Controllers\Panel\ParkingReportController;
use App\Http\Controllers\Panel\ParkingCancellationRequestController;
use App\Http\Controllers\Panel\ParkingExitController;
use App\Http\Controllers\Panel\ParkingLotController;
use App\Http\Controllers\Panel\ParkingRateController;
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

    // CRUD de planos (task-11, seção 4).
    Route::get('/planos', [AdminPlanController::class, 'index'])
        ->middleware('permission:payment-plans.index')->name('payment-plans.index');
    Route::get('/planos/criar', [AdminPlanController::class, 'create'])
        ->middleware('permission:payment-plans.create')->name('payment-plans.create');
    Route::post('/planos', [AdminPlanController::class, 'store'])
        ->middleware('permission:payment-plans.create')->name('payment-plans.store');
    Route::get('/planos/{plan}/editar', [AdminPlanController::class, 'edit'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.edit');
    Route::put('/planos/{plan}', [AdminPlanController::class, 'update'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.update');

    // Vantagens de marketing (plan_features) — dentro da tela de editar
    // plano (task-11, seção 4), mesma permission de payment-plans.edit.
    Route::post('/planos/{plan}/vantagens', [PlanFeatureController::class, 'store'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.features.store');
    Route::put('/planos/{plan}/vantagens/{feature}', [PlanFeatureController::class, 'update'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.features.update');
    Route::delete('/planos/{plan}/vantagens/{feature}', [PlanFeatureController::class, 'destroy'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.features.destroy');
    Route::post('/planos/{plan}/vantagens/{feature}/mover', [PlanFeatureController::class, 'move'])
        ->middleware('permission:payment-plans.edit')->name('payment-plans.features.move');

    // Catálogo de planos de repasse (task-9/11).
    Route::get('/planos-de-repasse', [PayoutPlanController::class, 'index'])
        ->middleware('permission:payout-plans.index')->name('payout-plans.index');
    Route::get('/planos-de-repasse/criar', [PayoutPlanController::class, 'create'])
        ->middleware('permission:payout-plans.create')->name('payout-plans.create');
    Route::post('/planos-de-repasse', [PayoutPlanController::class, 'store'])
        ->middleware('permission:payout-plans.create')->name('payout-plans.store');
    Route::get('/planos-de-repasse/{payout_plan}/editar', [PayoutPlanController::class, 'edit'])
        ->middleware('permission:payout-plans.edit')->name('payout-plans.edit');
    Route::put('/planos-de-repasse/{payout_plan}', [PayoutPlanController::class, 'update'])
        ->middleware('permission:payout-plans.edit')->name('payout-plans.update');

    // Assinantes e pedidos (task-11, seção 4).
    Route::get('/assinantes', [AdminSubscriptionController::class, 'index'])
        ->middleware('permission:users.index')->name('subscriptions.index');
    Route::get('/assinantes/{subscription}', [AdminSubscriptionController::class, 'show'])
        ->middleware('permission:users.index')->name('subscriptions.show');
    Route::get('/pedidos', [OrderController::class, 'index'])
        ->middleware('permission:orders.index')->name('orders.index');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])
        ->middleware('permission:orders.show')->name('orders.show');

    // Auditoria (task-11, seção 4).
    Route::get('/auditoria', [AuditController::class, 'index'])
        ->middleware('permission:audits.index')->name('audits.index');

    // Gerenciador de permissões — exclusivo do Super Admin (task-23).
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.index')->name('roles.index');
    Route::get('/roles/criar', [RoleController::class, 'create'])
        ->middleware('permission:roles.create')->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')->name('roles.store');
    Route::get('/roles/{role}/editar', [RoleController::class, 'edit'])
        ->middleware('permission:roles.edit')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.destroy')->name('roles.destroy');
    Route::post('/roles/update-order', [RoleController::class, 'updateOrder'])
        ->middleware('permission:roles.update-order')->name('roles.update-order');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.index')->name('permissions.index');
    Route::get('/permissions/criar', [PermissionController::class, 'create'])
        ->middleware('permission:permissions.create')->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create')->name('permissions.store');
    Route::get('/permissions/{permission}/editar', [PermissionController::class, 'edit'])
        ->middleware('permission:permissions.edit')->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware('permission:permissions.edit')->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware('permission:permissions.destroy')->name('permissions.destroy');

    Route::get('/role-permissions/{role}', [RolePermissionController::class, 'index'])
        ->middleware('permission:role-permissions.index')->name('role-permissions.index');
    Route::post('/role-permissions/{role}/{permission}', [RolePermissionController::class, 'update'])
        ->middleware('permission:role-permissions.update')->name('role-permissions.update');

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

    // Monetização do estacionamento (task-10, seção 8).
    Route::get('/configuracoes-estacionamento', [ParkingBillingSettingController::class, 'edit'])
        ->middleware('permission:parking-billing-settings.edit')->name('parking-billing-settings.edit');
    Route::put('/configuracoes-estacionamento', [ParkingBillingSettingController::class, 'update'])
        ->middleware('permission:parking-billing-settings.edit')->name('parking-billing-settings.update');
    Route::get('/cobrancas-estacionamento', [AdminParkingBillingChargeController::class, 'index'])
        ->middleware('permission:parking-billing-charges.index')->name('parking-billing-charges.index');
    Route::get('/cobrancas-estacionamento/{parking_billing_charge}', [AdminParkingBillingChargeController::class, 'show'])
        ->middleware('permission:parking-billing-charges.index')->name('parking-billing-charges.show');
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
    Route::post('/confirmar-lavagem/buscar', [WashConfirmationController::class, 'lookup'])
        ->name('panel.washes.confirm.lookup');
    Route::post('/confirmar-lavagem', [WashConfirmationController::class, 'confirm'])
        ->name('panel.washes.confirm.store');
    Route::get('/lavagens', [WashHistoryController::class, 'index'])->name('panel.washes.index');
    Route::post('/lavagens/{wash_redemption}/solicitar-cancelamento', [WashHistoryController::class, 'requestCancellation'])
        ->name('panel.washes.request-cancellation');

    // Repasses recebidos (task-9, seção 4).
    Route::get('/repasses', [PanelPayoutController::class, 'index'])->name('panel.payouts.index');
    Route::get('/repasses/{payout}', [PanelPayoutController::class, 'show'])->name('panel.payouts.show');

    // Módulo de estacionamento (task-10) — restrito a quem tem o
    // produto 'estacionamento' ativo.
    Route::middleware('parking-active')->prefix('estacionamento')->group(function () {
        Route::get('/', [ParkingLotController::class, 'show'])->name('panel.parking.sessions.index');
        Route::post('/', [ParkingLotController::class, 'store'])->name('panel.parking.lot.store');
        Route::get('/tarifas', [ParkingRateController::class, 'index'])->name('panel.parking.rates.index');
        Route::post('/tarifas', [ParkingRateController::class, 'store'])->name('panel.parking.rates.store');
        Route::get('/entrada', [ParkingEntryController::class, 'create'])->name('panel.parking.entry.create');
        Route::post('/entrada', [ParkingEntryController::class, 'store'])->name('panel.parking.entry.store');
        Route::get('/saida', [ParkingExitController::class, 'index'])->name('panel.parking.exit.index');
        Route::post('/{parking_session}/saida', [ParkingExitController::class, 'store'])->name('panel.parking.exit.store');
        Route::post('/{parking_session}/solicitar-cancelamento', [ParkingCancellationRequestController::class, 'store'])
            ->name('panel.parking.request-cancellation');
        Route::get('/cobrancas', [ParkingBillingChargeController::class, 'index'])->name('panel.parking.charges.index');
        Route::get('/cobrancas/{charge}/pagar', [ParkingBillingChargeController::class, 'checkout'])->name('panel.parking.charges.checkout');
        Route::post('/cobrancas/{charge}/pagar', [ParkingBillingChargeController::class, 'pay'])->name('panel.parking.charges.pay');
        Route::get('/relatorio', [ParkingReportController::class, 'index'])->name('panel.parking.report');
    });
});

// Aceite público de convite de equipe (task-5, seção 6).
Route::get('/convites/{token}', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/convites/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

require __DIR__.'/auth.php';
