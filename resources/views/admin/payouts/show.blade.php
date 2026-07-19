@extends('layouts.admin')

@section('title', 'Repasse — '.$payout->carWash->name)

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $payout->carWash->name }}</h1>
        <x-badge :status="$payout->status" />
    </div>

    <x-card class="mb-6">
        <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
            <div><dt class="inline font-medium">Período:</dt> <dd class="inline">{{ $payout->period_start->format('d/m/Y') }} – {{ $payout->period_end->format('d/m/Y') }}</dd></div>
            <div><dt class="inline font-medium">Valor total:</dt> <dd class="inline">R$ {{ number_format($payout->total_amount_cents / 100, 2, ',', '.') }}</dd></div>
            @if ($payout->payment_reference)
                <div><dt class="inline font-medium">Referência do pagamento:</dt> <dd class="inline">{{ $payout->payment_reference }}</dd></div>
            @endif
        </dl>

        @if ($payout->status === 'pending')
            <div class="flex items-center gap-3 mt-4">
                @can('payouts.mark-paid')
                    <x-confirm-modal :action="route('payouts.mark-paid', $payout)"
                                     title="Marcar repasse como pago?"
                                     message="O pagamento (PIX/transferência) já precisa ter sido feito fora do sistema."
                                     confirm-label="Marcar como pago">
                        <x-slot:trigger><button type="button" class="btn-primary">Marcar como pago</button></x-slot:trigger>
                        <x-form-field label="Referência da transferência" name="payment_reference" required />
                    </x-confirm-modal>
                @endcan
                @can('payouts.mark-failed')
                    <x-confirm-modal :action="route('payouts.mark-failed', $payout)"
                                     title="Marcar repasse como falhou?"
                                     message="Ex: dados bancários errados. Pode corrigir e tentar de novo no próximo lote."
                                     confirm-label="Marcar como falhou">
                        <x-slot:trigger><button type="button" class="btn-danger">Marcar como falhou</button></x-slot:trigger>
                    </x-confirm-modal>
                @endcan
            </div>
        @endif
    </x-card>

    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Lavagens do lote</h2>

    <x-data-table :rows="$payout->items" empty-message="Nenhum item">
        <x-slot:head>
            <x-data-table.th>Lavagem #</x-data-table.th>
            <x-data-table.th>Confirmada em</x-data-table.th>
            <x-data-table.th>Valor</x-data-table.th>
        </x-slot:head>

        @foreach ($payout->items as $item)
            <tr>
                <td class="px-4 py-3">#{{ $item->wash_redemption_id }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $item->washRedemption->redeemed_at?->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-3">R$ {{ number_format($item->amount_cents / 100, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </x-data-table>
@endsection
