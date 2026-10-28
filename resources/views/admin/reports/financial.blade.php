@extends('layouts.admin')

@section('title', 'Financial Report')

@section('content')

<x-page-header title="Financial Report" subtitle="Revenue, profit, and payment mix">
    <x-slot:actions>
        <a href="{{ route('admin.export.financial', ['from' => $from, 'to' => $to]) }}" target="_blank" class="btn-secondary">🖨 Print / PDF</a>
    </x-slot:actions>
</x-page-header>

<form method="GET" class="card p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="input">
        </div>
        <div>
            <label class="label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="input">
        </div>
        <div class="flex items-end gap-2 md:col-span-2">
            <button type="submit" class="btn-primary">Apply</button>
        </div>
    </div>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <x-stat-card
        label="Total Revenue"
        value="₱{{ number_format($revenue, 2) }}"
        icon="💰"
        accent="emerald"
    />

    <x-stat-card
        label="Gross Profit"
        value="₱{{ number_format($profit, 2) }}"
        icon="📈"
        accent="indigo"
    />

    <x-stat-card
        label="Transactions"
        :value="$salesCount"
        icon="🧾"
        accent="sky"
    />

    <x-stat-card
        label="Avg. Sale"
        value="₱{{ number_format($salesCount > 0 ? $revenue / $salesCount : 0, 2) }}"
        icon="📊"
        accent="amber"
    />

</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <x-section-card title="Payment Breakdown">
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span class="text-sm text-slate-700">Cash</span>
                </div>
                <x-money :amount="$cashTotal" class="font-medium" />
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                    <span class="text-sm text-slate-700">GCash</span>
                </div>
                <x-money :amount="$gcashTotal" class="font-medium" />
            </div>
            <div class="pt-4 border-t border-slate-200 flex justify-between items-center">
                <span class="text-sm font-semibold text-slate-800">Total</span>
                <x-money :amount="$cashTotal + $gcashTotal" class="font-bold" />
            </div>
        </div>
    </x-section-card>

    <x-section-card title="Profit Summary">
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Revenue</span>
                <x-money :amount="$revenue" class="font-medium" />
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Gross Profit</span>
                <x-money :amount="$profit" class="font-medium text-emerald-700" />
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Margin</span>
                <span class="text-sm font-medium">
                    {{ $revenue > 0 ? number_format(($profit / $revenue) * 100, 2) : '0.00' }}%
                </span>
            </div>
        </div>
    </x-section-card>

</div>

@endsection