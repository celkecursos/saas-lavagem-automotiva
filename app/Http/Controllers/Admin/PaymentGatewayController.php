<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentGatewayRequest;
use App\Http\Requests\Admin\UpdatePaymentGatewayRequest;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CRUD admin de gateways de pagamento (task-4, seção 4). "Ativar" é
 * ação própria e desativa os demais numa transação — mesmo padrão do
 * ai_providers.is_active do ecossistema Celke.
 */
class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGateway::with('type')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    public function create(): View
    {
        $types = PaymentGatewayType::orderBy('name')->get();

        return view('admin.payment-gateways.create', compact('types'));
    }

    public function store(StorePaymentGatewayRequest $request): RedirectResponse
    {
        // Nunca nasce ativo — ativar é ação própria (evita ativar sem
        // querer um gateway recém-criado com credencial errada).
        PaymentGateway::create([
            ...$request->validated(),
            'is_active' => false,
        ]);

        return redirect()->route('payment-gateways.index')
            ->with('success', 'Gateway de pagamento criado.');
    }

    public function edit(PaymentGateway $paymentGateway): View
    {
        $paymentGateway->load('type');

        return view('admin.payment-gateways.edit', ['gateway' => $paymentGateway]);
    }

    public function update(UpdatePaymentGatewayRequest $request, PaymentGateway $paymentGateway): RedirectResponse
    {
        $validated = $request->validated();

        // Token em branco = mantém as credenciais atuais.
        if (blank($validated['credentials']['token'] ?? null)) {
            unset($validated['credentials']);
        }

        $paymentGateway->update($validated);

        return redirect()->route('payment-gateways.index')
            ->with('success', 'Gateway de pagamento atualizado.');
    }

    public function activate(PaymentGateway $paymentGateway): RedirectResponse
    {
        DB::transaction(function () use ($paymentGateway) {
            // Ativar um desativa os demais (task-4, seção 1) — um por um
            // pra cada mudança ficar registrada em audits.
            PaymentGateway::where('is_active', true)
                ->whereKeyNot($paymentGateway->id)
                ->get()
                ->each(fn (PaymentGateway $other) => $other->update(['is_active' => false]));

            $paymentGateway->update(['is_active' => true]);
        });

        return redirect()->route('payment-gateways.index')
            ->with('success', 'Gateway ativado. Os demais foram desativados.');
    }
}
