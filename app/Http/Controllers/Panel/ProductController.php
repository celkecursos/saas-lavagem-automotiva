<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\CarWashProductSubscription;
use App\Models\PayoutPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
