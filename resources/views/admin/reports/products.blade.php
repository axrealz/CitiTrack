@extends('layouts.admin')

@section('title', 'Products Report')

@section('content')

<x-page-header title="Products Report" subtitle="Full product catalog snapshot">
    <x-slot:actions>
        <a href="{{ route('admin.export.inventory') }}" target="_blank" class="btn-secondary">🖨 Print / PDF</a>
    </x-slot:actions>
</x-page-header>

<div class="card overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Supplier</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Cost</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Price</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Stock</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            @foreach($products as $product)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-sm text-slate-800 font-medium">{{ $product->name }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $product->supplier->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-sm"><x-money :amount="$product->cost" /></td>
                    <td class="px-4 py-3 text-right text-sm font-medium"><x-money :amount="$product->price" /></td>
                    <td class="px-4 py-3 text-right text-sm">
                        <span class="{{ $product->isLowStock() ? 'text-amber-700 font-semibold' : '' }}">
                            {{ $product->stock_qty }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<x-pagination :paginator="$products" />

@endsection