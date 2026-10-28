@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<x-page-header title="Overview" subtitle="Live snapshot of your store" />

{{-- Stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <x-stat-card
        label="Today's Sales"
        value="₱{{ number_format($todaySales, 2) }}"
        icon="💰"
        accent="emerald"
    />

    <x-stat-card
        label="Today's Transactions"
        :value="$todayTransactions"
        icon="🧾"
        accent="indigo"
    />

    <x-stat-card
        label="This Month"
        value="₱{{ number_format($monthlyRevenue, 2) }}"
        icon="📈"
        accent="sky"
    />

    <x-stat-card
        label="Total Products"
        :value="$totalProducts"
        icon="📦"
        accent="indigo"
    />

    <x-stat-card
        label="Low Stock Items"
        :value="$lowStockCount"
        icon="⚠️"
        accent="amber"
    />

    <x-stat-card
        label="Active Staff"
        :value="$totalStaff"
        icon="👥"
        accent="sky"
    />

    <x-stat-card
        label="Pending Voids"
        :value="$pendingVoids"
        icon="🚫"
        accent="red"
    />

    <x-stat-card
        label="Pending Adjustments"
        :value="$pendingAdjustments"
        icon="🔧"
        accent="amber"
    />

</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Recent transactions --}}
    <x-section-card title="Recent Transactions" subtitle="Latest 5 completed sales">
        @if($recentTransactions->isEmpty())
            <x-empty-state title="No transactions yet" message="Sales will appear here once you start recording." />
        @else
            <table class="w-full text-sm">
                <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-2 text-left">Reference</th>
                        <th class="py-2 text-left">Cashier</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentTransactions as $tx)
                        <tr>
                            <td class="py-2 font-mono text-xs text-slate-700">{{ $tx->reference_no }}</td>
                            <td class="py-2 text-slate-700">{{ $tx->cashier->name ?? '—' }}</td>
                            <td class="py-2 text-right font-medium">₱{{ number_format($tx->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-section-card>

    {{-- Recent activity --}}
    <x-section-card title="Recent Activity" subtitle="Last 10 action log entries">
        @if($recentLogs->isEmpty())
            <x-empty-state title="No activity yet" />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($recentLogs as $log)
                    <x-timeline-item
                        :title="$log->action"
                        :time="$log->created_at->diffForHumans()"
                        :description="$log->user->name ?? 'System'"
                        accent="{{ str_contains($log->action, 'rejected') || str_contains($log->action, 'deleted') ? 'red' : 'indigo' }}"
                    />
                @endforeach
            </ul>
        @endif
    </x-section-card>

</div>

@endsection