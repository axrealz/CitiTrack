@extends('layouts.stocker')

@section('title', 'Inventory Dashboard')

@section('content')

<x-page-header title="Hello, {{ auth()->user()->name }}" subtitle="Inventory at a glance" />

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <x-stat-card
        label="Total Products"
        :value="$totalProducts"
        icon="📦"
        accent="sky"
    />

    <x-stat-card
        label="Low Stock Items"
        :value="$lowStockCount"
        icon="⚠️"
        accent="amber"
    />

    <x-stat-card
        label="Stock-Ins Today"
        :value="$todayStockIns"
        icon="📥"
        accent="emerald"
    />

    <x-stat-card
        label="Pending Adjustments"
        :value="$pendingAdjustments"
        icon="🔧"
        accent="indigo"
    />

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <x-section-card title="Quick Actions">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('stocker.stock-in.index') }}"
                   class="block p-5 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition text-center">
                    <div class="text-3xl mb-2">📥</div>
                    <div class="font-semibold text-sky-800">Record Stock-In</div>
                    <div class="text-xs text-sky-700 mt-1">Log a new delivery</div>
                </a>

                <a href="{{ route('stocker.low-stock') }}"
                   class="block p-5 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition text-center">
                    <div class="text-3xl mb-2">⚠️</div>
                    <div class="font-semibold text-amber-800">Low Stock</div>
                    <div class="text-xs text-amber-600 mt-1">Items to restock</div>
                </a>

                <a href="{{ route('stocker.adjustments.create') }}"
                   class="block p-5 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition text-center">
                    <div class="text-3xl mb-2">🔧</div>
                    <div class="font-semibold text-indigo-800">Adjustment</div>
                    <div class="text-xs text-indigo-600 mt-1">Request a stock fix</div>
                </a>
            </div>
        </x-section-card>

        <x-section-card title="Low Stock Products" subtitle="Items at or below reorder level">
            @if($lowStockProducts->isEmpty())
                <x-empty-state title="All stocked up" message="No products need restocking right now." />
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2 text-left">Product</th>
                            <th class="py-2 text-left">Supplier</th>
                            <th class="py-2 text-right">Stock</th>
                            <th class="py-2 text-right">Reorder</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($lowStockProducts as $product)
                            <tr>
                                <td class="py-2 font-medium text-slate-800">{{ $product->name }}</td>
                                <td class="py-2 text-slate-600 text-xs">{{ $product->supplier->name ?? '—' }}</td>
                                <td class="py-2 text-right font-semibold text-amber-700">{{ $product->stock_qty }}</td>
                                <td class="py-2 text-right text-slate-500">{{ $product->reorder_level }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <a href="{{ route('stocker.low-stock') }}"
                       class="text-sm text-sky-700 hover:text-sky-900 font-medium">View all →</a>
                </div>
            @endif
        </x-section-card>

    </div>

    <div class="space-y-6">
        <x-section-card title="Recent Stock-Ins" subtitle="Your last 5 deliveries">
            @if($recentStockIns->isEmpty())
                <p class="text-sm text-slate-500">No deliveries recorded yet.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($recentStockIns as $log)
                        <li class="py-2 flex justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-medium text-slate-700 truncate">
                                    {{ $log->product->name ?? '—' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $log->supplier->name ?? 'No supplier' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="text-emerald-700 font-mono text-sm whitespace-nowrap">
                                +{{ $log->qty }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-section-card>
    </div>

</div>

@endsection