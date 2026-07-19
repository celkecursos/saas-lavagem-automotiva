<x-guest-layout title="Confirmar senha — Celke Wash Club">
    <x-slot:heading>Confirmar senha</x-slot:heading>
    <x-slot:subheading>
        Esta é uma área restrita. Confirme sua senha para continuar.
    </x-slot:subheading>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password"
                          placeholder="••••••••" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
            Confirmar
        </button>
    </form>
</x-guest-layout>
