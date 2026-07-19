@extends('layouts.admin')

@section('title', 'Pedido #'.$order->id.' — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Pedido #{{ $order->id }}</h1>
        <x-badge :status="$order->status" />
    </div>

    <x-card>
        <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
            <div><dt class="inline font-medium">Usuário:</dt> <dd class="inline">{{ $order->user->name }} ({{ $order->user->email }})</dd></div>
            <div><dt class="inline font-medium">Valor:</dt> <dd class="inline">R$ {{ number_format($order->amount_cents / 100, 2, ',', '.') }}</dd></div>
            <div><dt class="inline font-medium">Gateway:</dt> <dd class="inline">{{ $order->paymentGateway?->label ?? '—' }}</dd></div>
            @if ($order->recurring_type)
                <div><dt class="inline font-medium">Tipo:</dt> <dd class="inline">{{ $order->recurring_type }}</dd></div>
            @endif
            @if ($order->external_reference)
                <div><dt class="inline font-medium">Referência externa:</dt> <dd class="inline">{{ $order->external_reference }}</dd></div>
            @endif
            @if ($order->paid_at)
                <div><dt class="inline font-medium">Pago em:</dt> <dd class="inline">{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
            @endif
            <div><dt class="inline font-medium">Criado em:</dt> <dd class="inline">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
        </dl>
    </x-card>

    @if ($order->refundRequest)
        <x-card title="Reembolso" class="mt-4">
            <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                <div><dt class="inline font-medium">Status:</dt> <dd class="inline"><x-badge :status="$order->refundRequest->status" /></dd></div>
                <div><dt class="inline font-medium">Solicitado por:</dt> <dd class="inline">{{ $order->refundRequest->requestedBy->name }} ({{ $order->refundRequest->initiated_by === 'admin' ? 'admin' : 'self-service' }})</dd></div>
                <div><dt class="inline font-medium">Motivo:</dt> <dd class="inline">{{ $order->refundRequest->reason }}</dd></div>
                @if ($order->refundRequest->processed_at)
                    <div><dt class="inline font-medium">Processado em:</dt> <dd class="inline">{{ $order->refundRequest->processed_at->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>
        </x-card>
    @elseif ($order->status === 'paid' && auth()->user()->can('orders.refund'))
        <x-card title="Reembolso" class="mt-4">
            <form method="POST" action="{{ route('orders.refund', $order) }}">
                @csrf
                <x-form-field label="Motivo do reembolso" name="reason">
                    <textarea name="reason" id="reason" rows="3" required
                              class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-gray-900 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </x-form-field>
                <button type="submit" class="btn-danger">Reembolsar</button>
            </form>
        </x-card>
    @endif
@endsection
