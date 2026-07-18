<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Painel do admin (URLs em /admin, nomes internos em inglês — ver
// convenção de idioma na orientacao.txt).
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('admin.dashboard');
});
