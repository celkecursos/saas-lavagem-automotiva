<x-guest-layout title="Entrar — Celke Wash Club">
    <x-slot:heading>Entrar na sua conta</x-slot:heading>
    <x-slot:subheading>Acesse o painel para resgatar lavagens e gerenciar sua assinatura.</x-slot:subheading>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email"
                          :value="old('email')" placeholder="voce@exemplo.com.br"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Senha" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-blue-600 hover:underline dark:text-blue-400">Esqueceu a senha?</a>
                @endif
            </div>
            <x-text-input id="password" class="mt-1.5 block w-full" type="password" name="password"
                          placeholder="••••••••" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-blue-500 shadow-sm focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
            <span class="text-sm text-gray-600 dark:text-gray-400">Manter conectado</span>
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
            Entrar
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Criar conta</a>
        </p>
    @endif
</x-guest-layout>
