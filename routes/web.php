<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Panel\CarWashSwitchController;
use App\Http\Controllers\Panel\DashboardController as PanelDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Painel do admin (URLs em /admin, nomes internos em inglês — ver
// convenção de idioma na orientacao.txt).
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.dashboard');
});

// Painel do lava-rápido (URLs em /painel, ver convenção de idioma).
// SetCurrentCarWash resolve/valida o lava-rápido atual da sessão
// (task-5, seção 7).
Route::middleware(['auth', 'car-wash'])->prefix('painel')->group(function () {
    Route::get('/', PanelDashboardController::class)->name('panel.dashboard');
    Route::post('/trocar-lava-rapido', CarWashSwitchController::class)->name('panel.car-wash.switch');
});
