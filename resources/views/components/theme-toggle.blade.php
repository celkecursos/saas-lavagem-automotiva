{{-- Toggle dark/light (task-18): escolha manual persiste em
     localStorage e passa a valer sempre; sem escolha prévia, segue o
     prefers-color-scheme do sistema. Preferência de dispositivo/browser,
     não de conta — nada no banco na v1. --}}
<button type="button"
        x-data="{
            dark: localStorage.getItem('theme')
                ? localStorage.getItem('theme') === 'dark'
                : window.matchMedia('(prefers-color-scheme: dark)').matches
        }"
        x-init="$watch('dark', v => {
            document.documentElement.classList.toggle('dark', v);
            localStorage.setItem('theme', v ? 'dark' : 'light');
        }); document.documentElement.classList.toggle('dark', dark)"
        @click="dark = !dark"
        {{ $attributes->merge(['class' => 'text-gray-400 hover:text-gray-200 md:text-gray-500 md:hover:text-gray-700 md:dark:text-gray-400 md:dark:hover:text-gray-200 cursor-pointer']) }}
        aria-label="Alternar tema claro/escuro">
    {{-- Lua (mostrada no tema claro — clique ativa o escuro) --}}
    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
    </svg>
    {{-- Sol (mostrado no tema escuro — clique volta pro claro) --}}
    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
    </svg>
</button>
