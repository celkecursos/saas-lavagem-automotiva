<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Troca o "lava-rápido atual" da sessão (seletor do topo do painel —
 * task-5, seção 7 / task-14, seção 3).
 */
class CarWashSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'car_wash_id' => ['required', 'integer'],
        ]);

        // Só permite trocar pra um car_wash que o usuário realmente tem.
        $hasAccess = DB::table('car_wash_users')
            ->where('user_id', $request->user()->id)
            ->where('car_wash_id', $validated['car_wash_id'])
            ->exists();

        abort_unless($hasAccess, 403);

        $request->session()->put('current_car_wash_id', (int) $validated['car_wash_id']);

        return redirect()->route('panel.dashboard');
    }
}
