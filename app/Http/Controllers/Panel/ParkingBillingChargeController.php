<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\ParkingBillingCharge;
use App\Services\Payment\PagSeguroGateway;
use App\Services\Payment\PagSeguroPublicKeyProvider;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentGatewayNotConfiguredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Histórico de cobranças do estacionamento (task-10, seção 8) e
 * checkout embedded pra pagar uma cobrança pendente — mesmo fluxo da
 * task-4 (seção 5.3).
 */
class ParkingBillingChargeController extends Controller
{
    public function index(): View
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        $charges = ParkingBillingCharge::where('car_wash_id', $carWash->id)
            ->latest('period_start')
            ->paginate(12);

        return view('panel.parking.charges', compact('charges'));
    }

    public function checkout(ParkingBillingCharge $charge): View
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));
        abort_unless($charge->car_wash_id === $carWash->id, 403);
        abort_unless($charge->status === 'pending', 404);

        $gateway = PaymentGatewayFactory::resolveActiveGateway();
        $publicKey = $gateway === null ? null : PagSeguroPublicKeyProvider::for($gateway);

        if ($publicKey === null) {
            return view('panel.parking.charge-checkout-unavailable');
        }

        return view('panel.parking.charge-checkout', compact('charge', 'publicKey'));
    }

    public function pay(Request $request, ParkingBillingCharge $charge): JsonResponse
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));
        abort_unless($charge->car_wash_id === $carWash->id, 403);
        abort_unless($charge->status === 'pending', 404);

        $validated = $request->validate(['encrypted_card' => ['required', 'string']]);

        $order = $charge->order;

        try {
            $service = PaymentGatewayFactory::make();
        } catch (PaymentGatewayNotConfiguredException $e) {
            return response()->json(['message' => 'Pagamentos indisponíveis no momento.'], 503);
        }

        if ($service instanceof PagSeguroGateway) {
            $service->setEncryptedCard($validated['encrypted_card']);
        }

        $result = $service->createCheckout($order);
        $syncStatus = $result->embeddedData['status'] ?? null;

        if ($syncStatus === 'paid') {
            $order->update(['status' => 'paid', 'paid_at' => now(), 'external_reference' => $result->externalReference]);
            $charge->update(['status' => 'paid']);

            return response()->json(['redirect' => route('panel.parking.charges.index')]);
        }

        $order->update(['status' => 'failed', 'external_reference' => $result->externalReference]);

        return response()->json([
            'message' => $result->embeddedData['failure_reason'] ?? 'Pagamento recusado.',
        ], 422);
    }
}
