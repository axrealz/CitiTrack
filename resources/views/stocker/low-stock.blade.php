@extends('layouts.stocker')

@section('title', 'Low Stock')

@section('content')

<x-page-header title="Low Stock Items" subtitle="Products at or below reorder level" />

@if($products->isEmpty())
    <x-empty-state title="All stocked up" message="No products need restocking right now." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Preferred Supplier</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Current Stock</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Reorder Level</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Shortage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($products as $product)
                    <tr class="hover:bg-amber-50">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $product->category->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $product->supplier->name ?? '— Not assigned —' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-amber-700">
                            {{ $product->stock_qty }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">{{ $product->reorder_level }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono text-red-600">
                            -{{ $product->reorder_level - $product->stock_qty }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection