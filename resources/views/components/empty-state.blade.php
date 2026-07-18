{{-- Ícone + mensagem curta, usado em toda listagem vazia (task-14,
     seção 6). --}}
@props(['message' => 'Nenhum registro encontrado'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-10 text-center']) }}>
    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
</div>
