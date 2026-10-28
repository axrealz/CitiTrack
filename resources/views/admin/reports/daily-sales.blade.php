@extends('layouts.admin')

@section('title', 'Daily Sales Report')

@section('content')

<x-page-header title="Daily Sales Report" subtitle="Totals per day over a selected range" />

<form method="GET" class="card p-4 mb-4">
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
            <a href="{{ route('admin.export.sales', ['from' => $from, 'to' => $to]) }}"
                class="btn-secondary" target="_blank">
                🖨 Print / PDF
            </a>
        </div>
    </div>
</form>

@if($sales->isEmpty())
    <x-empty-state title="No sales in this range" message="Try a different date range." />
@else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card
            label="Total Revenue"
            value="₱{{ number_format($sales->sum('total'), 2) }}"
            icon="💰"
            accent="emerald"
        />
        <x-stat-card
            label="Total Transactions"
            :value="$sales->sum('count')"
            icon="🧾"
            accent="indigo"
        />
        <x-stat-card
            label="Days Covered"
            :value="$sales->count()"
            icon="📅"
            accent="sky"
        />
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Transactions</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Average Sale</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($sales as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ \Carbon\Carbon::parse($row->date)->format('M d, Y (D)') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">{{ $row->count }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <x-money :amount="$row->total" />
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">
                            <x-money :amount="$row->count > 0 ? $row->total / $row->count : 0" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200">
                <tr>
                    <td class="px-4 py-3 text-sm font-semibold text-slate-700">Total</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold">{{ $sales->sum('count') }}</td>
                    <td class="px-4 py-3 text-right text-sm font-semibold">
                        <x-money :amount="$sales->sum('total')" />
                    </td>
                    <td class="px-4 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif

@endsection