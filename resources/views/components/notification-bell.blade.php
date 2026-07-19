{{-- Sino de notificações in-app (task-19, seção 3) — canal 'database'
     das Notifications nativas do Laravel. Dropdown é renderizado no
     load (últimas 5) e o contador atualiza por polling simples (sem
     WebSocket/Reverb na v1). --}}
@auth
    <div x-data="notificationBell()" x-init="init()" class="relative" @click.outside="open = false">
        <button type="button" @click="open = !open"
                class="relative text-gray-400 hover:text-gray-200 md:text-gray-500 md:hover:text-gray-700 md:dark:text-gray-400 md:dark:hover:text-gray-200 cursor-pointer"
                aria-label="Notificações">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span x-show="count > 0" x-cloak x-text="count > 9 ? '9+' : count"
                  class="absolute -top-1.5 -right-1.5 min-w-[1.1rem] h-[1.1rem] px-1 flex items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-semibold"></span>
        </button>

        <div x-show="open" x-cloak
             class="absolute right-0 mt-2 w-80 max-w-[90vw] rounded-lg shadow-lg bg-white dark:bg-backgroundseconddark border border-gray-200 dark:border-gray-800 z-50">
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 text-sm font-semibold text-gray-700 dark:text-gray-300">
                Notificações
            </div>

            <template x-if="items.length === 0">
                <p class="px-4 py-6 text-sm text-gray-500 dark:text-gray-400 text-center">Nenhuma notificação ainda.</p>
            </template>

            <template x-for="item in items" :key="item.id">
                <a href="#" @click.prevent="open = false; markRead(item)"
                   class="block px-4 py-3 border-b border-gray-100 dark:border-gray-800 last:border-b-0 hover:bg-gray-50 dark:hover:bg-backgroundthirddark">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.title"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="item.body"></p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="item.time"></p>
                </a>
            </template>

            <a href="{{ route('notifications.index') }}"
               class="block text-center text-sm text-blue-600 dark:text-blue-400 px-4 py-2 hover:underline">
                Ver todas
            </a>
        </div>
    </div>

    @once
        <script>
            function notificationBell() {
                return {
                    open: false,
                    count: 0,
                    items: [],
                    init() {
                        this.fetchData();
                        setInterval(() => this.fetchData(), 30000);
                    },
                    fetchData() {
                        fetch('{{ route('notifications.index') }}', { headers: { Accept: 'application/json' } })
                            .then((response) => response.json())
                            .then((data) => {
                                this.count = data.unread_count;
                                this.items = data.items;
                            })
                            .catch(() => {});
                    },
                    markRead(item) {
                        fetch(`/notificacoes/${item.id}/marcar-lida`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                Accept: 'application/json',
                            },
                        }).finally(() => {
                            window.location.href = item.url;
                        });
                    },
                };
            }
        </script>
    @endonce
@endauth
