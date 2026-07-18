<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve o "lava-rápido atual" das rotas /painel/* (task-5, seção 7):
 * todas operam sobre UM car_wash por vez. Com 1 vínculo só, assume
 * automático; com vários, respeita o current_car_wash_id da sessão
 * (trocado pelo seletor do topo) — sempre validando que o usuário
 * logado realmente tem acesso àquele car_wash.
 */
class SetCurrentCarWash
{
    public function handle(Request $request, Closure $next): Response
    {
        $linkedIds = DB::table('car_wash_users')
            ->where('user_id', $request->user()->id)
            ->pluck('car_wash_id');

        // Sem nenhum vínculo, não há painel de lava-rápido pra mostrar.
        abort_if($linkedIds->isEmpty(), 403);

        $current = $request->session()->get('current_car_wash_id');

        if (! $linkedIds->contains($current)) {
            $request->session()->put('current_car_wash_id', $linkedIds->first());
        }

        return $next($request);
    }
}
