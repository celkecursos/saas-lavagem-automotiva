<?php

namespace App\Http\Middleware;

use App\Models\CarWash;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Só o car_wash com o produto 'estacionamento' ativo pode acessar o
 * módulo de estacionamento (task-10). Roda depois de 'car-wash'
 * (SetCurrentCarWash já resolveu current_car_wash_id).
 */
class EnsureParkingActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasActiveParking = CarWash::whereKey(session('current_car_wash_id'))
            ->whereHas('productSubscriptions', fn ($query) => $query
                ->where('product', 'estacionamento')
                ->where('status', 'active'))
            ->exists();

        abort_unless($hasActiveParking, 403);

        return $next($request);
    }
}
