@extends('layouts.admin')

@section('title', 'Inventory Summary')

@section('content')

<x-page-header title="Inventory Summary" subtitle="Stock value and quantity snapshot">
    <x-slot:actions>
        <a href="{{ route('admin.export.inventory') }}" target="_blank" class="btn-secondary">🖨 Print / PDF</a>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card
        label="Total Stock Value (at cost)"
        value="₱{{ number_format($totalStockValue, 2) }}"
        icon="💰"
        accent="emerald"
    />
    <x-stat-card
        label="Distinct Products"
        :value="$products->total()"
        icon="📦"
        accent="indigo"
    />
    <x-stat-card
        label="Low-Stock Items"
        :value="$products->where('stock_qty', '<=', 5)->count()"
        icon="⚠️"
        accent="amber"
    />
</div>

<div class="card overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Category</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Stock</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Cost</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Value</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @foreach($products as $product)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <span class="{{ $product->isLowStock() ? 'text-amber-700 font-semibold' : '' }}">
                            {{ $product->stock_qty }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm"><x-money :amount="$product->cost" /></td>
                    <td class="px-4 py-3 text-right text-sm font-medium">
                        <x-money :amount="$product->stock_qty * $product->cost" />
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<x-pagination :paginator="$products" />

@endsection