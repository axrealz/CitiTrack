@extends('layouts.admin')

@section('title', 'Products')

@section('content')

<x-page-header title="Products" subtitle="Manage all items in the store">
    <x-slot:actions>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">+ Add Product</a>
    </x-slot:actions>
</x-page-header>

{{-- Filters --}}
<form method="GET" class="card p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-search-input name="q" placeholder="Search by name or SKU..." />

        <x-filter-select
            name="category"
            :options="$categories->pluck('name', 'id')->toArray()"
            placeholder="All Categories"
        />

        <select name="stock_status" class="input" aria-label="Stock status">
            <option value="">All Products</option>
            <option value="in"  @selected(request('stock_status') === 'in')>In Stock</option>
            <option value="low" @selected(request('stock_status') === 'low')>Low Stock</option>
            <option value="out" @selected(request('stock_status') === 'out')>Out of Stock</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Reset</a>
        </div>
    </div>
</form>

@if($products->isEmpty())
    <x-empty-state title="No products found" message="Try adjusting your filters or add a new product." />
@else
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Category</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Cost</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Price</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Stock</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Stock Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $product->name }}</div>
                            @if($product->sku)
                                <div class="text-xs font-mono text-slate-500">{{ $product->sku }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            {{ $product->category->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-slate-600">
                            <x-money :amount="$product->cost" />
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-medium text-slate-800">
                            <x-money :amount="$product->price" />
                        </td>

                        {{-- Stock quantity + reorder level --}}
                        <td class="px-4 py-3 text-right text-sm">
                            <span class="{{ ['in' => 'text-slate-700', 'low' => 'text-amber-700 font-bold', 'out' => 'text-red-700 font-bold'][$product->stock_status] }}">
                                {{ $product->stock_qty }}
                            </span>
                            <div class="text-xs text-slate-500">Reorder at {{ $product->reorder_level }}</div>
                        </td>

                        {{-- Stock Status badge --}}
                        <td class="px-4 py-3 text-center">
                            @if($product->stock_status === 'out')
                                <x-badge color="red">Out of Stock</x-badge>
                            @elseif($product->stock_status === 'low')
                                <x-badge color="amber">Low Stock</x-badge>
                            @else
                                <x-badge color="green">In Stock</x-badge>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($product->is_active)
                                <x-badge color="green">Active</x-badge>
                            @else
                                <x-badge color="slate">Inactive</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.products.show', $product) }}"
                               class="text-sky-700 hover:text-sky-900 text-sm font-medium">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium ml-3">Edit</a>
                            <span class="ml-3">
                                <x-delete-button :action="route('admin.products.destroy', $product)" entity="product" :subject="$product->name" />
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$products" />
@endif

@endsection