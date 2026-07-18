<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-100 dark:bg-backgrounddark min-h-screen">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">
        {{-- Overlay do menu mobile (task-14, seção 7). --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-30 bg-black/50 md:hidden"></div>

        {{-- Sidebar do admin (itens na task-14, seção 2); colapsa em
             hambúrguer abaixo de md (task-14, seção 7). --}}
        <aside class="fixed inset-y-0 left-0 z-40 flex flex-col w-64 shrink-0 bg-backgroundsecond transform transition-transform -translate-x-full md:static md:translate-x-0"
               :class="sidebarOpen && 'translate-x-0'">
            <div class="flex items-center gap-3 px-4 py-4">
                {{-- Logo 500x500 sempre com dimensão explícita (task-6, seção 3.1). --}}
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-10 h-10">
                <span class="text-gray-200 font-semibold">Admin</span>
            </div>
            <nav class="flex-1 overflow-y-auto px-2 py-2 space-y-1">
                @include('layouts.partials.admin-sidebar')
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            <header class="flex items-center justify-between bg-backgroundsecond px-4 py-3 md:bg-white md:dark:bg-backgroundseconddark md:border-b md:border-gray-200 md:dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <button type="button" @click="sidebarOpen = true"
                            class="md:hidden text-gray-400 hover:text-gray-200 cursor-pointer"
                            aria-label="Abrir menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    @yield('topbar-left')
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <span class="text-sm text-gray-400 md:text-gray-600 md:dark:text-gray-300">{{ auth()->user()->name }}</span>
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-400 hover:text-gray-200 md:text-gray-500 md:hover:text-gray-700 md:dark:hover:text-gray-300 cursor-pointer">Sair</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </header>

            <main class="flex-1 p-4 md:p-6">
                @if (session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
