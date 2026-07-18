<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarWashProductSubscription;
use App\Notifications\ClubActivationApproved;
use App\Notifications\ClubActivationRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Fila de ativação do clube de lavagem (task-5, seção 5; task-9/11):
 * o lava-rápido escolheu um payout_plan e aguarda o admin aprovar.
 * Ações auditáveis via model.
 */
class CarWashProductSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $subscriptions = CarWashProductSubscription::query()
            ->where('product', 'clube_lavagem')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->with(['carWash', 'payoutPlan'])
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.car-wash-product-subscriptions.index', compact('subscriptions', 'status'));
    }

    public function approve(Request $request, CarWashProductSubscription $subscription): RedirectResponse
    {
        // Sem payout_plan escolhido o produto não pode virar 'active'
        // (task-3, seção 2).
        abort_if($subscription->payout_plan_id === null, 422);

        $subscription->update([
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
            'approved_by' => $request->user()->id,
        ]);

        $this->notifyOwners($subscription, new ClubActivationApproved($subscription));

        return redirect()->route('car-wash-product-subscriptions.index')
            ->with('success', 'Clube de lavagem ativado pro lava-rápido.');
    }

    public function reject(CarWashProductSubscription $subscription): RedirectResponse
    {
        $subscription->update(['status' => 'canceled']);

        $this->notifyOwners($subscription, new ClubActivationRejected($subscription));

        return redirect()->route('car-wash-product-subscriptions.index')
            ->with('success', 'Solicitação rejeitada; o lava-rápido foi avisado.');
    }

    private function notifyOwners(CarWashProductSubscription $subscription, object $notification): void
    {
        $owners = $subscription->carWash->users()->wherePivot('role', 'owner')->get();

        Notification::send($owners, $notification);
    }
}
