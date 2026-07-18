@extends('layouts.public')

@section('title', 'Minhas indicações — Celke Wash Club')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Minhas indicações</h1>

        <x-card title="Seu código de indicação">
            <div x-data="{
                    copied: false,
                    link: @js(route('register', ['ref' => $referralCode])),
                    copy() {
                        navigator.clipboard.writeText(this.link);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    },
                }"
                 class="flex items-center gap-3">
                <code class="flex-1 px-3 py-2 rounded-lg bg-gray-100 dark:bg-backgroundthirddark text-sm text-gray-900 dark:text-gray-100 truncate" x-text="link"></code>
                <button type="button" @click="copy()" class="btn-secondary shrink-0">
                    <span x-show="!copied">Copiar link</span>
                    <span x-show="copied" x-cloak>Copiado!</span>
                </button>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Código: <strong>{{ $referralCode }}</strong>
            </p>
        </x-card>

        <x-stat-tile label="Lavagens grátis ganhas por indicação" :value="$grantedCount" class="mt-4" />

        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mt-6 mb-3">Indicações feitas</h2>

        @if ($referrals->isEmpty())
            <x-card>
                <x-empty-state message="Você ainda não indicou ninguém." />
            </x-card>
        @else
            <x-data-table :rows="$referrals">
                <x-slot:head>
                    <x-data-table.th>Indicado</x-data-table.th>
                    <x-data-table.th>Status</x-data-table.th>
                </x-slot:head>

                @foreach ($referrals as $referral)
                    <tr>
                        <td class="px-4 py-3">{{ $referral->referred->name }}</td>
                        <td class="px-4 py-3"><x-badge :status="$referral->status" /></td>
                    </tr>
                @endforeach
            </x-data-table>
        @endif
    </div>
@endsection
