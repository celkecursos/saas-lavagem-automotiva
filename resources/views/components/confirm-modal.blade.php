{{-- Confirmação antes de ações destrutivas/irreversíveis — aprovar,
     rejeitar, suspender, marcar como pago (task-14, seção 6).

     Uso:
       <x-confirm-modal :action="route('payouts.mark-paid', $payout)"
                        title="Marcar como pago?"
                        message="Essa ação não pode ser desfeita.">
           <x-slot:trigger>
               <button type="button" class="btn-primary">Marcar como pago</button>
           </x-slot:trigger>
           (campos extras opcionais do form aqui)
       </x-confirm-modal>
--}}
@props([
    'action',
    'method' => 'POST',
    'title' => 'Confirmar ação?',
    'message' => 'Essa ação não pode ser desfeita.',
    'confirmLabel' => 'Confirmar',
    'cancelLabel' => 'Cancelar',
])

<div x-data="{ open: false }" class="inline-block">
    <span @click="open = true">
        {{ $trigger }}
    </span>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50" @click="open = false"></div>
            <div class="relative bg-white dark:bg-backgroundseconddark rounded-lg shadow-lg max-w-md w-full p-6"
                 @keydown.escape.window="open = false">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>

                <form method="POST" action="{{ $action }}" class="mt-4">
                    @csrf
                    @if (! in_array(strtoupper($method), ['GET', 'POST']))
                        @method($method)
                    @endif

                    {{ $slot }}

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn-secondary" @click="open = false">{{ $cancelLabel }}</button>
                        <button type="submit" class="btn-danger">{{ $confirmLabel }}</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
