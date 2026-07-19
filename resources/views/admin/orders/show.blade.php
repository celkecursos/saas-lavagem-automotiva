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
@endsection
