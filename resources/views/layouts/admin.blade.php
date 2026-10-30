<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CitiTrack') }} — Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50">

<div class="h-screen flex overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col no-print h-screen">

        <div class="px-5 py-4 border-b border-slate-800">
            <div class="font-bold text-white leading-tight">CitiTrack</div>
            <div class="text-xs text-indigo-400 uppercase tracking-wide">Admin</div>
        </div>

        <nav class="no-scrollbar flex-1 overflow-y-auto px-3 py-2 space-y-0.5 text-sm">

            <x-sidebar-link route="admin.dashboard" icon="home" label="Dashboard" />

            <div class="px-3 pt-3 pb-0.5 text-xs uppercase text-slate-400 tracking-wider">Catalog</div>
            <x-sidebar-link route="admin.products.index"   icon="cube"  label="Products" />
            <x-sidebar-link route="admin.categories.index" icon="tag"   label="Categories" />
            <x-sidebar-link route="admin.suppliers.index"  icon="truck" label="Suppliers" />

            <div class="px-3 pt-3 pb-0.5 text-xs uppercase text-slate-400 tracking-wider">Approvals</div>
            <x-sidebar-link route="admin.voids.index"       icon="ban"    label="Void Requests" />
            <x-sidebar-link route="admin.adjustments.index" icon="adjust" label="Adjustments" />

            <div class="px-3 pt-3 pb-0.5 text-xs uppercase text-slate-400 tracking-wider">Operations</div>
            <x-sidebar-link route="admin.users.index"      icon="users" label="Staff" />
            <x-sidebar-link route="admin.audit-logs.index" icon="log"   label="Action Logs" />

            <div class="px-3 pt-3 pb-0.5 text-xs uppercase text-slate-400 tracking-wider">Insights</div>
            <x-sidebar-link route="admin.reports.index" icon="chart" label="Reports" />

        </nav>

        <div class="px-4 py-2.5 border-t border-slate-800 text-sm">
            <div class="text-white font-medium">{{ auth()->user()->name }}</div>
            <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
            <div class="mt-2 flex gap-4 text-sm">
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