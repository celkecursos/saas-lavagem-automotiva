<x-guest-layout title="Recuperar senha — Celke Wash Club">
    <x-slot:heading>Recuperar senha</x-slot:heading>
    <x-slot:subheading>
        Informe o e-mail da sua conta e enviaremos um link para você cadastrar uma nova senha.
    </x-slot:subheading>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email"
                          :value="old('email')" placeholder="voce@exemplo.com.br" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
            Enviar link de recuperação
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
        Lembrou a senha?
        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Entrar</a>
    </p>
</x-guest-layout>
