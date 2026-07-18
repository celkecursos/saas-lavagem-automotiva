<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Correção e reenvio de cadastro rejeitado (task-5, seção 3): o dono
 * ajusta os dados do estabelecimento e o cadastro volta pra 'pending'.
 */
class RegistrationController extends Controller
{
    public function edit(Request $request): View
    {
        $carWash = $this->rejectedCarWashOrAbort($request);

        return view('panel.registration-edit', compact('carWash'));
    }

    public function update(Request $request): RedirectResponse
    {
        $carWash = $this->rejectedCarWashOrAbort($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:18', 'unique:car_washes,document,'.$carWash->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:car_washes,email,'.$carWash->id],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'zip_code' => ['required', 'string', 'max:9'],
        ]);

        $carWash->update([
            ...$validated,
            'state' => Str::upper($validated['state']),
            // Reenvio: volta pra fila do admin (task-5, seção 3).
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return redirect()->route('panel.dashboard')
            ->with('success', 'Cadastro reenviado para análise.');
    }

    private function rejectedCarWashOrAbort(Request $request): CarWash
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        // Só o owner corrige o cadastro; só quando rejeitado.
        $isOwner = $carWash->users()
            ->wherePivot('role', 'owner')
            ->whereKey($request->user()->id)
            ->exists();

        abort_unless($isOwner, 403);
        abort_unless($carWash->status === 'rejected', 403);

        return $carWash;
    }
}
