@extends('layouts.car-wash-panel')

@section('title', 'Equipe — Painel')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Equipe</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="Membros">
            <ul class="text-sm space-y-2 text-gray-700 dark:text-gray-300">
                @foreach ($members as $member)
                    <li class="flex items-center justify-between">
                        <span>{{ $member->name }} <span class="text-gray-500 dark:text-gray-400">({{ $member->email }})</span></span>
                        <x-badge :status="$member->pivot->role" variant="primary">{{ $member->pivot->role === 'owner' ? 'dono' : 'funcionário' }}</x-badge>
                    </li>
                @endforeach
            </ul>

            @if ($pendingInvitations->isNotEmpty())
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mt-4 mb-2">Convites pendentes</h4>
                <ul class="text-sm space-y-1 text-gray-500 dark:text-gray-400">
                    @foreach ($pendingInvitations as $invitation)
                        <li>{{ $invitation->email }} — expira {{ $invitation->expires_at->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Convidar por e-mail">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Quem já tem conta é vinculado na hora; quem não tem recebe um
                convite por e-mail válido por 7 dias.
            </p>
            <form method="POST" action="{{ route('panel.team.invite') }}">
                @csrf
                <x-form-field label="E-mail" name="email" type="email" :value="old('email')" required />
                <button type="submit" class="btn-primary">Convidar</button>
            </form>
        </x-card>
    </div>
@endsection
