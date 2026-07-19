<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia QUALQUER usuário suspenso (admin, dono/funcionário de
 * lava-rápido, assinante), mesmo com sessão já ativa — não só no login
 * (task-22, seção 4). Roda em toda request web, sem custo extra pra
 * visitante deslogado.
 */
class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->suspended_at !== null) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Sua conta foi suspensa. Entre em contato com o suporte.');
        }

        return $next($request);
    }
}
