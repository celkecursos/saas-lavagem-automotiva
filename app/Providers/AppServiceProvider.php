<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bypass total do Super Admin: passa em QUALQUER ability sem
        // precisar de cada permission atribuída uma a uma (task-3,
        // seção 6 / task-23, replicando o padrão do projeto adm).
        // Retornar null (não false) mantém a checagem normal pros demais.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
