<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CitiTrack') }} — Cashier</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50">

<div class="h-screen flex overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col no-print h-screen">

        <div class="px-5 py-5 border-b border-slate-800">
            <div class="font-bold text-white leading-tight">CitiTrack</div>
            <div class="text-xs text-emerald-400 uppercase tracking-wide">Cashier</div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1 text-sm">

            <x-sidebar-link route="cashier.dashboard" icon="home" label="Dashboard" />

            <div class="px-3 pt-4 pb-1 text-xs uppercase text-slate-400 tracking-wider">Sales</div>
            <x-sidebar-link route="cashier.pos"             icon="cart"  label="Point of Sale" />
            <x-sidebar-link route="cashier.sales-history"   icon="list"  label="Sales History" />

            <div class="px-3 pt-4 pb-1 text-xs uppercase text-slate-400 tracking-wider">Post-Sale</div>
            <x-sidebar-link route="cashier.returns.index"   icon="reply" label="Returns" />
            <x-sidebar-link route="cashier.voids.index"     icon="ban"   label="Void Requests" />

        </nav>

        <div class="px-4 py-3 border-t border-slate-800 text-sm">
            <div class="text-white font-medium">{{ auth()->user()->name }}</div>
            <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            <div class="mt-3 flex gap-4 text-sm">
                <a href="{{ route('profile.edit') }}"
                   class="text-slate-300 hover:text-amber-400 font-medium transition">
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-red-300 hover:text-red-200 font-medium transition">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0 h-screen">

        <header class="h-14 bg-white border-b border-slate-200 flex items-center px-6 no-print shrink-0">
            <h1 class="text-lg font-semibold text-slate-800">
                @yield('title', 'Dashboard')
            </h1>
        </header>

        <div class="flex-1 overflow-y-auto">
            <div class="px-6 pt-4">
                <x-alert type="success" :message="session('success')" />
                <x-alert type="error"   :message="session('error')" />
            </div>

            <main class="p-6">
                @yield('content')
            </main>
        </div>

    </div>
</div>

</body>
</html>