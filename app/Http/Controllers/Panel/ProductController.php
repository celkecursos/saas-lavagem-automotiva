<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\PayoutPlan;
use App\Notifications\NewClubActivationRequest;
use App\Support\AdminRecipients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * "Meus produtos" do painel do lava-rápido (task-5, seção 5).
 * Estacionamento: 100% self-service. Clube de lavagem: escolhe um
 * payout_plan e cai na fila de aprovação do admin (envolve dinheiro
 * repassado pela plataforma).
 */
class ProductController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $carWash = $this->currentCarWash();

        // Só depois de aprovado o painel libera "Meus produtos"
        // (task-5, seções 3 e 5).
        if ($carWash->status !== 'approved') {
            return redirect()->route('panel.dashboard')
                ->with('error', 'Os produtos só ficam disponíveis depois que o cadastro for aprovado.');
        }

        $subscriptions = $carWash->productSubscriptions()->get()->keyBy('product');

        return view('panel.products', [
            'carWash' => $carWash,
            'clube' => $subscriptions->get('clube_lavagem'),
            'estacionamento' => $subscriptions->get('estacionamento'),
            'payoutPlans' => PayoutPlan::where('active', true)
                ->orderBy('category')->orderBy('level')->get(),
            'isOwner' => $this->isOwner($request),
        ]);
    }

    /**
     * Estacionamento: ativa na hora, sem aprovação extra (task-5, §5).
     */
    public function activateParking(Request $request): RedirectResponse
    {
        $carWash = $this->authorizeOwnerAction($request);

        CarWashProductSubscription::updateOrCreate(
            ['car_wash_id' => $carWash->id, 'product' => 'estacionamento'],
            ['status' => 'active', 'activated_at' => now(), 'suspended_at' => null],
        );

        return redirect()->route('panel.products.index')
            ->with('success', 'Gerenciador de estacionamento ativado.');
    }

    /**
     * Pausa por iniciativa própria — não precisa de aprovação pra
     * pausar, só pra (re)ativar no caso do clube (task-5, §5).
     */
    public function pauseParking(Request $request): RedirectResponse
    {
        $carWash = $this->authorizeOwnerAction($request);

        $carWash->productSubscriptions()
            ->where('product', 'estacionamento')
            ->where('status', 'active')
            ->get()
            ->each(fn (CarWashProductSubscription $subscription) => $subscription
                ->update(['status' => 'suspended', 'suspended_at' => now()]));

        return redirect()->route('panel.products.index')
            ->with('success', 'Gerenciador de estacionamento pausado.');
    }

    /**
     * Clube de lavagem: o dono ESCOLHE um payout_plan do catálogo (sem
     * valor livre) e a solicitação cai na fila de aprovação do admin —
     * envolve dinheiro repassado pela plataforma (task-5, seção 5).
     * Trocar de plano depois volta pra 'pending' até nova aprovação.
     */
    public function requestClub(Request $request): RedirectResponse
    {
        $carWash = $this->authorizeOwnerAction($request);

        $validated = $request->validate([
            'payout_plan_id' => ['required', 'integer', 'exists:payout_plans,id'],
        ], [
            'payout_plan_id.required' => 'Escolha um plano de repasse pra solicitar a ativação.',
        ]);

        abort_unless(
            PayoutPlan::whereKey($validated['payout_plan_id'])->where('active', true)->exists(),
            422,
        );

        $subscription = CarWashProductSubscription::updateOrCreate(
            ['car_wash_id' => $carWash->id, 'product' => 'clube_lavagem'],
            [
                'status' => 'pending',
                'payout_plan_id' => $validated['payout_plan_id'],
                'activated_at' => null,
                'suspended_at' => null,
                'approved_by' => null,
            ],
        );

        Notification::send(
            AdminRecipients::withPermission('car-wash-product-subscriptions.approve'),
            new NewClubActivationRequest($subscription),
        );

        return redirect()->route('panel.products.index')
            ->with('success', 'Solicitação enviada — aguardando aprovação da plataforma.');
    }

    public function pauseClub(Request $request): RedirectResponse
    {
        $carWash = $this->authorizeOwnerAction($request);

        $carWash->productSubscriptions()
            ->where('product', 'clube_lavagem')
            ->where('status', 'active')
            ->get()
            ->each(fn (CarWashProductSubscription $subscription) => $subscription
                ->update(['status' => 'suspended', 'suspended_at' => now()]));

        return redirect()->route('panel.products.index')
            ->with('success', 'Clube de lavagem pausado.');
    }

    private function currentCarWash(): CarWash
    {
        return CarWash::findOrFail(session('current_car_wash_id'));
    }

    private function isOwner(Request $request): bool
    {
        return $this->currentCarWash()->users()
            ->wherePivot('role', 'owner')
            ->whereKey($request->user()->id)
            ->exists();
    }

    private function authorizeOwnerAction(Request $request): CarWash
    {
        $carWash = $this->currentCarWash();

        // Só o dono contrata/pausa produtos; e só com cadastro aprovado.
        abort_unless($this->isOwner($request), 403);
        abort_unless($carWash->status === 'approved', 403);

        return $carWash;
    }
}
