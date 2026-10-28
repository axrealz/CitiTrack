@extends('layouts.cashier')

@section('title', 'Cashier Dashboard')

@section('content')

<x-page-header title="Hello, {{ auth()->user()->name }}" subtitle="Your shift at a glance" />

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <x-stat-card
        label="Today's Sales"
        value="₱{{ number_format($todaySales, 2) }}"
        icon="💰"
        accent="emerald"
    />

    <x-stat-card
        label="Today's Completed Sales"
        :value="$todayTransactions"
        icon="🧾"
        accent="indigo"
    />

    <x-stat-card
        label="This Month"
        value="₱{{ number_format($monthlySales, 2) }}"
        icon="📈"
        accent="sky"
    />

    <x-stat-card
        label="Pending Voids"
        :value="$pendingVoids"
        icon="🚫"
        accent="amber"
    />

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2">
        <x-section-card title="Quick Actions">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('cashier.pos') }}"
                   class="block p-5 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition text-center">
                    <div class="text-3xl mb-2">🛒</div>
                    <div class="font-semibold text-emerald-800">Open POS</div>
                    <div class="text-xs text-emerald-700 mt-1">Start a new sale</div>
                </a>

                <a href="{{ route('cashier.returns.index') }}"
                   class="block p-5 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition text-center">
                    <div class="text-3xl mb-2">↩️</div>
                    <div class="font-semibold text-amber-800">Returns</div>
                    <div class="text-xs text-amber-600 mt-1">Process a return</div>
                </a>

                <a href="{{ route('cashier.voids.index') }}"
                   class="block p-5 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition text-center">
                    <div class="text-3xl mb-2">🚫</div>
                    <div class="font-semibold text-red-800">Void Requests</div>
                    <div class="text-xs text-red-600 mt-1">Request a void</div>
                </a>
            </div>
        </x-section-card>

        <div class="mt-6">
            <x-section-card title="Recent Transactions" subtitle="Your last 5 sales">
                @if($recentTransactions->isEmpty())
                    <x-empty-state title="No sales yet today" message="Open the POS to record your first sale.">
                        <a href="{{ route('cashier.pos') }}" class="btn-primary">Open POS</a>
                    </x-empty-state>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-2 text-left">Reference</th>
                                <th class="py-2 text-left">Date</th>
                                <th class="py-2 text-right">Total</th>
                                <th class="py-2 text-right">Status</th>
                                <th class="py-2 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentTransactions as $tx)
                                <tr>
                                    <td class="py-2 font-mono text-xs">{{ $tx->reference_no }}</td>
                                    <td class="py-2 text-slate-600 text-xs">
                                        {{ $tx->created_at->format('M d, g:i A') }}
                                    </td>
                                    <td class="py-2 text-right font-medium">
                                        <x-money :amount="$tx->total" />
                                    </td>
                                    <td class="py-2 text-right">
                                        @php
                                            $statusColors = ['completed' => 'green', 'voided' => 'red', 'returned' => 'amber'];
                                        @endphp
                                        <x-badge :color="$statusColors[$tx->status] ?? 'slate'">
                                            {{ ucfirst($tx->status) }}
                                        </x-badge>
                                    </td>
                                    <td class="py-2 text-right">
                                        <a href="{{ route('cashier.sales-history.show', $tx) }}"
                                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-section-card>
        </div>
    </div>

    <div>
        <x-section-card title="Shift Info">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Logged in</dt>
                    <dd class="text-slate-800">{{ now()->format('M d, Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Current time</dt>
                    <dd class="text-slate-800">{{ now()->format('g:i A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Role</dt>
                    <dd><x-badge color="emerald">Cashier</x-badge></dd>
                </div>
            </dl>
        </x-section-card>
    </div>

</div>

@endsection