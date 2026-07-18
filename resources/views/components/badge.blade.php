{{--
    Badge de status do domínio (task-6, seções 2 e 4).

    Resolve a cor automaticamente a partir do mapeamento status -> cor
    da task-6, evitando bg-*-100/text-*-800 decorado à mão nas views.

    Uso:
      <x-badge :status="$carWash->status" />
      <x-badge :status="$order->status" variant="secondary" />  (override
        pros casos ambíguos entre models — ex: 'canceled' é danger na
        maioria, mas secondary em orders; 'approved' é success em
        cancellation_requests, mas info em order_refund_requests)
      <x-badge :status="$status">Rótulo custom</x-badge>
--}}
@props(['status', 'variant' => null])

@php
    $variant ??= match ($status) {
        'approved', 'active', 'paid', 'completed', 'granted', 'processed', 'free' => 'success',
        'pending', 'past_due', 'failed_manual' => 'warning',
        'rejected', 'canceled', 'failed', 'chargeback' => 'danger',
        'incomplete', 'refunded', 'requested', 'open', 'qualified' => 'info',
        'suspended', 'expired', 'closed' => 'secondary',
        default => 'secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge-'.$variant]) }}>{{ $slot->isEmpty() ? $status : $slot }}</span>
