@extends('layouts.public')

@section('title', 'Pedido #'.$order->id.' — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pedido #{{ $order->id }}</h1>
            <x-badge :status="$order->status" />
        </div>

        <x-card>
            <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                <div><dt class="inline font-medium">Valor:</dt> <dd class="inline">R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}</dd></div>
                @if ($order->paid_at)
                    <div><dt class="inline font-medium">Pago em:</dt> <dd class="inline">{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
                @endif
                <div><dt class="inline font-medium">Criado em:</dt> <dd class="inline">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </x-card>

        @if ($order->refundRequest)
            <x-card title="Reembolso" class="mt-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Reembolso solicitado — status: <x-badge :status="$order->refundRequest->status" />
                </p>
            </x-card>
        @elseif (app(\App\Services\Refund\RefundService::class)->isSelfServiceEligible($order))
            <x-card title="Reembolso" class="mt-4">
                <form method="POST" action="{{ route('order.request-refund', $order) }}">
                    @csrf
                    <x-form-field label="Motivo do reembolso" name="reason">
                        <textarea name="reason" id="reason" rows="3" required
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-gray-900 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </x-form-field>
                    <button type="submit" class="btn-danger">Solicitar reembolso</button>
                </form>
            </x-card>
        @endif
    </div>
@endsection
