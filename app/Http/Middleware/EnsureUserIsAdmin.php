<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe as rotas /admin/* a staff da plataforma (users.role='admin')
 * — a autorização fina de cada ação continua sendo por permission
 * (spatie), este middleware só corta quem nem é do time (ver task-3,
 * seção 1, sobre o papel do campo role).
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return $next($request);
    }
}
