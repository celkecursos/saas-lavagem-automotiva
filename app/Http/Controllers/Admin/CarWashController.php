<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Notifications\CarWashApproved;
use App\Notifications\CarWashRejected;
use App\Notifications\CarWashSuspended;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Fila de aprovação de lava-rápidos (task-5, seção 4). Cada ação tem
 * rota própria (não é efeito colateral de update genérico) e é
 * auditável via model CarWash.
 */
class CarWashController extends Controller
{
    public function index(Request $request): View
    {
        // Padrão da fila: 'pending', com e-mail do owner verificado
        // primeiro (não é status novo — só ordenação, task-5 seção 2).
        $status = $request->query('status', 'pending');

        $carWashes = CarWash::query()
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->addSelect(['owner_email_verified_at' => DB::table('car_wash_users')
                ->join('users', 'users.id', '=', 'car_wash_users.user_id')
                ->whereColumn('car_wash_users.car_wash_id', 'car_washes.id')
                ->where('car_wash_users.role', 'owner')
                ->select('users.email_verified_at')
                ->limit(1),
            ])
            ->orderByRaw('owner_email_verified_at IS NULL')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.car-washes.index', compact('carWashes', 'status'));
    }

    public function show(CarWash $carWash): View
    {
        $carWash->load('users');

        return view('admin.car-washes.show', compact('carWash'));
    }

    public function approve(Request $request, CarWash $carWash): RedirectResponse
    {
        $carWash->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'rejection_reason' => null,
        ]);

        $this->notifyOwners($carWash, new CarWashApproved($carWash));

        return redirect()->route('car-washes.show', $carWash)
            ->with('success', 'Lava-rápido aprovado.');
    }

    public function reject(Request $request, CarWash $carWash): RedirectResponse
    {
        // Motivo obrigatório — é mostrado pro dono (task-5, seção 4).
        $validated = $request->validate(
            ['rejection_reason' => ['required', 'string', 'max:2000']],
            ['rejection_reason.required' => 'Informe o motivo da rejeição — ele é mostrado ao responsável.'],
        );

        $carWash->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $this->notifyOwners($carWash, new CarWashRejected($carWash));

        return redirect()->route('car-washes.show', $carWash)
            ->with('success', 'Cadastro rejeitado; o responsável foi avisado do motivo.');
    }

    public function suspend(CarWash $carWash): RedirectResponse
    {
        // Não apaga produtos contratados — só tira do ar (toda query
        // pública checa status='approved', ver task-8).
        $carWash->update(['status' => 'suspended']);

        $this->notifyOwners($carWash, new CarWashSuspended($carWash));

        return redirect()->route('car-washes.show', $carWash)
            ->with('success', 'Lava-rápido suspenso.');
    }

    private function notifyOwners(CarWash $carWash, object $notification): void
    {
        $owners = $carWash->users()->wherePivot('role', 'owner')->get();

        Notification::send($owners, $notification);
    }
}
