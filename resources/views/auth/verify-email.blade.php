<x-guest-layout title="Confirmar e-mail — Celke Wash Club">
    <x-slot:heading>Confirme seu e-mail</x-slot:heading>
    <x-slot:subheading>
        Enviamos um link de confirmação para o e-mail cadastrado. Clique nele para
        liberar sua conta. Não recebeu? A gente reenvia.
    </x-slot:subheading>

    @if (session('status') == 'verification-link-sent')
        <div class="alert-success">
            Um novo link de confirmação foi enviado para o seu e-mail.
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg bg-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-600">
                Reenviar e-mail de confirmação
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full text-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
