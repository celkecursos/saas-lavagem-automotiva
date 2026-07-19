@extends('layouts.admin')

@section('title', 'Cobrança — '.$charge->carWash->name)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $charge->carWash->name }}</h1>
        <x-badge :status="$charge->status" />
    </div>

    @if ($charge->flagged_for_review)
        <div class="alert-warning">
            Sinalizado pelo antifraude: volume de sessões incompatível com a
            capacidade declarada de vagas.
        </div>
    @endif

    <x-card class="mt-4">
        <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
            <div><dt class="inline font-medium">Período:</dt> <dd class="inline">{{ $charge->period_start->format('d/m/Y') }} – {{ $charge->period_end->format('d/m/Y') }}</dd></div>
            <div><dt class="inline font-medium">Lavagens no período:</dt> <dd class="inline">{{ $charge->wash_count }}</dd></div>
            <div><dt class="inline font-medium">Vagas declaradas:</dt> <dd class="inline">{{ $charge->total_spots_snapshot }}</dd></div>
            <div><dt class="inline font-medium">Sessões fechadas:</dt> <dd class="inline">{{ $charge->parking_sessions_count }}</dd></div>
            @if (! $charge->is_free)
                <div><dt class="inline font-medium">Percentual aplicado:</dt> <dd class="inline">{{ $charge->fee_percentage_applied }}%</dd></div>
                <div><dt class="inline font-medium">Valor:</dt> <dd class="inline">R$ {{ number_format($charge->fee_amount_cents / 100, 2, ',', '.') }}</dd></div>
            @endif
        </dl>
    </x-card>
@endsection
