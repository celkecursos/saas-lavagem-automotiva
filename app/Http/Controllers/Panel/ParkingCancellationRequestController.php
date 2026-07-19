<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CancellationRequest;
use App\Models\CarWash;
use App\Models\ParkingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Solicitação de cancelamento de sessão de estacionamento já fechada
 * (task-10, seção 4.1) — mesmo padrão da task-8/9 pro clube de
 * lavagem: fica pending até o admin decidir.
 */
class ParkingCancellationRequestController extends Controller
{
    public function store(Request $request, ParkingSession $parkingSession): RedirectResponse
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        abort_unless($parkingSession->parkingLot->car_wash_id === $carWash->id, 403);
        abort_unless($parkingSession->status === 'closed', 422, 'Só é possível solicitar cancelamento de uma sessão já fechada.');

        $hasPending = CancellationRequest::where('requestable_type', ParkingSession::class)
            ->where('requestable_id', $parkingSession->id)
            ->where('status', 'pending')
            ->exists();

        abort_if($hasPending, 422, 'Já existe uma solicitação de cancelamento pendente pra essa sessão.');

        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        CancellationRequest::create([
            'requestable_type' => ParkingSession::class,
            'requestable_id' => $parkingSession->id,
            'requested_by_user_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('panel.parking.exit.index')
            ->with('success', 'Solicitação de cancelamento enviada. A plataforma vai analisar.');
    }
}
