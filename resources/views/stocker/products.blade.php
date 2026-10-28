@extends('layouts.stocker')

@section('title', 'Products')

@section('content')

@isset($product)
    {{-- ─── Product detail view ─── --}}
    <x-page-header :title="$product->name" :subtitle="$product->sku ?? 'Product details'">
            <x-slot:actions>
                <a href="{{ route('stocker.products.index') }}" class="btn-secondary">← Back to list</a>
            </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-section-card title="Details">
                <dl class="grid grid-cols-2 gap-y-3 text-sm">
                    <dt class="text-slate-500">Category</dt>
                    <dd class="text-slate-800">{{ $product->category->name ?? '—' }}</dd>

                    <dt class="text-slate-500">Supplier</dt>
                    <dd class="text-slate-800">{{ $product->supplier->name ?? '—' }}</dd>

                    <dt class="text-slate-500">Cost</dt>
                    <dd class="text-slate-800"><x-money :amount="$product->cost" /></dd>

                    <dt class="text-slate-500">Price</dt>
                    <dd class="text-slate-800 font-medium"><x-money :amount="$product->price" /></dd>

                    <dt class="text-slate-500">Current Stock</dt>
                    <dd class="{{ $product->isLowStock() ? 'text-amber-700 font-semibold' : 'text-slate-800' }}">
                        {{ $product->stock_qty }}
                    </dd>

                    <dt class="text-slate-500">Reorder Level</dt>
                    <dd class="text-slate-800">{{ $product->reorder_level }}</dd>
                </dl>
            </x-section-card>
        </div>

        <div>
            <x-section-card title="Stock History" subtitle="Last 20 movements">
                @if($stockHistory->isEmpty())
                    <p class="text-sm text-slate-500">No movements yet.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach($stockHistory as $log)
                            <li class="py-2 flex justify-between gap-2">
                                <div>
                                    <div class="text-xs font-medium text-slate-700 capitalize">
                                        {{ str_replace('_', ' ', $log->type) }}
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        {{ $log->created_at->format('M d, g:i A') }}
                                    </div>
                                </div>
                                <div class="{{ $log->qty > 0 ? 'text-emerald-700' : 'text-red-600' }} font-mono text-sm">
                                    {{ $log->qty > 0 ? '+' : '' }}{{ $log->qty }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-section-card>
        </div>
    </div>

@else
    {{-- ─── Product list view ─── --}}
    <x-page-header title="Products" subtitle="View current inventory (read-only)" />

    <form method="GET" class="card p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <x-search-input name="q" placeholder="Search by name or SKU..." />
            </div>

            <select name="low_stock" class="input">
                <option value="">All Stock</option>
                <option value="1" @selected(request('low_stock') === '1')>Low Stock Only</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('stocker.products.index') }}" class="btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    @if($products->isEmpty())
        <x-empty-state title="No products found" />
    @else
        <div class="card overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Supplier</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase">Reorder</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($products as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">{{ $p->name }}</div>
                                @if($p->sku)
                                    <div class="text-xs font-mono text-slate-500">{{ $p->sku }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $p->supplier->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if($p->stock_qty == 0)
                                    <x-badge color="red" size="xs">Out of Stock</x-badge>
                                @elseif($p->stock_qty <= $p->reorder_level)
                                    <span class="text-amber-700 font-bold" title="Below reorder level ({{ $p->reorder_level }})">
                                        {{ $p->stock_qty }}
                                    </span>
                                @else
                                    <span class="text-slate-700">{{ $p->stock_qty }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-slate-500">{{ $p->reorder_level }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('stocker.products.show', $p) }}"
                                   class="text-sky-700 hover:text-sky-900 text-sm font-medium">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$products" />
    @endif
@endisset

@endsection