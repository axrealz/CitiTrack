@extends('layouts.stocker')

@section('title', 'Shipment Logs')

@section('content')

<x-page-header title="Shipment Logs" subtitle="All stock-in deliveries, with purchase cost and payment" />

<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <x-filter-select
            name="supplier"
            :options="$suppliers->pluck('name', 'id')->toArray()"
            placeholder="All Suppliers"
        />
        <div>
            <input type="date" name="from" value="{{ request('from') }}" class="input" placeholder="From">
        </div>
        <div>
            <input type="date" name="to" value="{{ request('to') }}" class="input" placeholder="To">
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('stocker.shipment-logs') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($logs->isEmpty())
    <x-empty-state title="No shipments found" message="Try adjusting your filters or record a new stock-in." />
@else
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Recorded by</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Qty</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Stock After</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Unit Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Paid</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Balance</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Reference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('M d, Y g:i A') }}
                            @if($log->purchase_date)
                                <div class="text-slate-600">Purchased {{ $log->purchase_date->format('M d, Y') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">
                            {{ $log->product->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $log->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono text-emerald-700">
                            +{{ $log->qty }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $log->stock_after }}</td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @if($log->unit_cost)
                                <x-money :amount="$log->unit_cost" />
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @if($log->total_cost !== null)
                                <x-money :amount="$log->total_cost" />
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @if($log->amount_paid !== null)
                                <x-money :amount="$log->amount_paid" />
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @if($log->balance !== null)
                                <x-money :amount="$log->balance" />
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($log->payment_status)
                                <x-badge :color="['unpaid' => 'red', 'partial' => 'amber', 'paid' => 'green'][$log->payment_status] ?? 'slate'">
                                    {{ $log->payment_status_label }}
                                </x-badge>
                                @if($log->payment_method_label)
                                    <div class="text-xs text-slate-600 mt-1">{{ $log->payment_method_label }}</div>
                                @endif
                            @else
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 font-mono">{{ $log->reference_no ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <x-pagination :paginator="$logs" />
@endif

@endsection