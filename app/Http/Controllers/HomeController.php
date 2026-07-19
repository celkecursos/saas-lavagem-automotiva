<?php

namespace App\Http\Controllers;

use App\Models\CarWash;
use App\Models\Plan;
use Illuminate\View\View;

/**
 * Home institucional (task-12, seção 2) — hierarquia visual proposital:
 * assinante em destaque total, parceria em segundo plano. Reaproveita
 * as MESMAS queries de /planos (task-7) e da listagem de lava-rápidos
 * do resgate (task-8), sem duplicar lógica nem inventar cache — reflete
 * mudanças do admin na próxima requisição.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->where('active', true)
            ->with(['features' => fn ($query) => $query->where('active', true)->orderBy('sort_order')])
            ->get();

        $carWashes = CarWash::query()
            ->where('status', 'approved')
            ->whereHas('productSubscriptions', fn ($query) => $query
                ->where('product', 'clube_lavagem')
                ->where('status', 'active'))
            ->orderBy('name')
            ->get();

        return view('home', compact('plans', 'carWashes'));
    }
}
