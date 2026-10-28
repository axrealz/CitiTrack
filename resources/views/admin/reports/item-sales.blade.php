@extends('layouts.admin')

@section('title', 'Item-Level Sales')

@section('content')

<x-page-header title="Item-Level Sales" subtitle="Best-selling products in the selected range" />

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
        </div>
    </div>
</form>

@if($items->isEmpty())
    <x-empty-state title="No sales in this range" />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Qty Sold</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-sm text-slate-800 font-medium">{{ $item->product_name }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ $item->total_qty }}</td>
                        <td class="px-4 py-3 text-right text-sm font-medium">
                            <x-money :amount="$item->total_sales" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$items" />
@endif

@endsection