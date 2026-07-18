<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-100 dark:bg-backgrounddark min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar do lava-rápido (itens na task-14, seção 3). --}}
        <aside class="hidden md:flex md:flex-col w-64 shrink-0 bg-backgroundsecond">
            <div class="flex items-center gap-3 px-4 py-4">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-10 h-10">
                <span class="text-gray-200 font-semibold">Painel</span>
            </div>
            <nav class="flex-1 overflow-y-auto px-2 py-2 space-y-1">
                @include('layouts.partials.car-wash-sidebar')
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar: seletor de lava-rápido atual quando o usuário tem
                 mais de 1 vínculo (task-5, seção 7 / task-14, seção 3). --}}
            <header class="flex items-center justify-between bg-backgroundsecond px-4 py-3 md:bg-white md:dark:bg-backgroundseconddark md:border-b md:border-gray-200 md:dark:border-gray-800">
                <div class="flex items-center gap-3">
                    @yield('topbar-left')
                    @include('layouts.partials.car-wash-selector')
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
