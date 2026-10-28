@extends('layouts.cashier')

@section('title', 'My Sales History')

@section('content')

<x-page-header title="Sales History" subtitle="All transactions you've processed" />

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2">
            <x-search-input name="q" placeholder="Search by reference number..." />
        </div>
        <div>
            <input type="date" name="from" value="{{ request('from') }}" class="input" placeholder="From">
        </div>
        <div>
            <input type="date" name="to" value="{{ request('to') }}" class="input" placeholder="To">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('cashier.sales-history') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($transactions->isEmpty())
    <x-empty-state title="No transactions found" message="Try adjusting your filters.">
        <a href="{{ route('cashier.pos') }}" class="btn-primary">Open POS</a>
    </x-empty-state>
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reference</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Items</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($transactions as $tx)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $tx->reference_no }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $tx->created_at->format('M d, Y g:i A') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">{{ $tx->items->count() }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <x-money :amount="$tx->total" />
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['completed' => 'green', 'voided' => 'red', 'returned' => 'amber'];
                            @endphp
                            <x-badge :color="$statusColors[$tx->status] ?? 'slate'">
                                {{ ucfirst($tx->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('cashier.sales-history.show', $tx) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$transactions" />
@endif

@endsection