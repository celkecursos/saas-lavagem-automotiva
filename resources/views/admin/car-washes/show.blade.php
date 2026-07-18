@extends('layouts.admin')

@section('title', $carWash->name.' — Admin')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $carWash->name }}</h1>
        <x-badge :status="$carWash->status" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="Estabelecimento">
            <dl class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                <div><dt class="inline font-medium">CNPJ/CPF:</dt> <dd class="inline">{{ $carWash->document }}</dd></div>
                <div><dt class="inline font-medium">E-mail:</dt> <dd class="inline">{{ $carWash->email }}</dd></div>
                <div><dt class="inline font-medium">Telefone:</dt> <dd class="inline">{{ $carWash->phone ?: '—' }}</dd></div>
                <div><dt class="inline font-medium">Endereço:</dt> <dd class="inline">{{ $carWash->address_line }}, {{ $carWash->city }}/{{ $carWash->state }} — CEP {{ $carWash->zip_code }}</dd></div>
                @if ($carWash->rejection_reason)
                    <div><dt class="inline font-medium">Motivo da rejeição:</dt> <dd class="inline">{{ $carWash->rejection_reason }}</dd></div>
                @endif
            </dl>
        </x-card>

        <x-card title="Equipe vinculada">
            <ul class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                @foreach ($carWash->users as $member)
                    <li class="flex items-center justify-between">
                        <span>
                            {{ $member->name }}
                            <span class="text-gray-500 dark:text-gray-400">({{ $member->email }})</span>
                            @unless ($member->email_verified_at)
                                <x-badge status="unverified" variant="warning">e-mail não verificado</x-badge>
                            @endunless
                        </span>
                        <x-badge :status="$member->pivot->role" variant="primary">{{ $member->pivot->role === 'owner' ? 'dono' : 'funcionário' }}</x-badge>
                    </li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <div class="flex flex-wrap items-center gap-3 mt-6">
        @if ($carWash->status === 'pending')
            @can('car-washes.approve')
                <x-confirm-modal :action="route('car-washes.approve', $carWash)"
                                 title="Aprovar este lava-rápido?"
                                 message="Ele poderá ativar produtos e aparecer para os assinantes."
                                 confirm-label="Aprovar">
                    <x-slot:trigger><button type="button" class="btn-primary">Aprovar</button></x-slot:trigger>
                </x-confirm-modal>
            @endcan
            @can('car-washes.reject')
                <x-confirm-modal :action="route('car-washes.reject', $carWash)"
                                 title="Rejeitar este cadastro?"
                                 message="O motivo abaixo será enviado ao responsável."
                                 confirm-label="Rejeitar">
                    <x-slot:trigger><button type="button" class="btn-danger">Rejeitar</button></x-slot:trigger>
                    <textarea name="rejection_reason" rows="3" required
                              placeholder="Motivo da rejeição (obrigatório)"
                              class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100"></textarea>
                </x-confirm-modal>
            @endcan
        @elseif ($carWash->status === 'approved')
            @can('car-washes.suspend')
                <x-confirm-modal :action="route('car-washes.suspend', $carWash)"
                                 title="Suspender este lava-rápido?"
                                 message="Ele deixa de aparecer pros assinantes e não pode operar enquanto suspenso."
                                 confirm-label="Suspender">
                    <x-slot:trigger><button type="button" class="btn-danger">Suspender</button></x-slot:trigger>
                </x-confirm-modal>
            @endcan
        @elseif ($carWash->status === 'suspended')
            @can('car-washes.approve')
                <x-confirm-modal :action="route('car-washes.approve', $carWash)"
                                 title="Reativar este lava-rápido?"
                                 message="Ele volta a aparecer pros assinantes."
                                 confirm-label="Reativar">
                    <x-slot:trigger><button type="button" class="btn-primary">Reativar</button></x-slot:trigger>
                </x-confirm-modal>
            @endcan
        @endif

        <a href="{{ route('car-washes.index') }}" class="btn-secondary">Voltar pra fila</a>
    </div>
@endsection
