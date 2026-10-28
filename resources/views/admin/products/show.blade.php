@extends('layouts.admin')

@section('title', $product->name)

@section('content')

<x-page-header :title="$product->name" :subtitle="$product->sku ?? 'No SKU'">
    <x-slot:actions>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary">Edit Product</a>
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

                <dt class="text-slate-500">Profit per unit</dt>
                <dd class="text-emerald-700 font-medium"><x-money :amount="$product->profit()" /></dd>

                <dt class="text-slate-500">Stock</dt>
                <dd>
                    @if($product->stock_qty == 0)
                        <x-badge color="red" size="xs">Out of Stock</x-badge>
                    @elseif($product->isLowStock())
                        <span class="text-amber-600 font-bold">{{ $product->stock_qty }}</span>
                        <span class="text-xs text-slate-500 ml-2">(reorder at {{ $product->reorder_level }})</span>
                    @else
                        <span class="text-slate-800">{{ $product->stock_qty }}</span>
                    @endif
                </dd>

                <dt class="text-slate-500">Reorder Level</dt>
                <dd class="text-slate-800">{{ $product->reorder_level }}</dd>

                <dt class="text-slate-500">Status</dt>
                <dd>
                    @if($product->is_active)
                        <x-badge color="green">Active</x-badge>
                    @else
                        <x-badge color="slate">Inactive</x-badge>
                    @endif
                </dd>
            </dl>
        </x-section-card>

        @if($product->description)
            <x-section-card title="Description">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $product->description }}</p>
            </x-section-card>
        @endif

    </div>

    <div class="space-y-6">
        <x-section-card title="Actions">
            <div class="space-y-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary w-full justify-center">
                    Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn-secondary w-full justify-center">
                    Back to list
                </a>
            </div>
        </x-section-card>

        <x-section-card title="Recent Stock Movements" subtitle="Last 10 changes">
            @if($product->stockLogs->isEmpty())
                <p class="text-sm text-slate-500">No movements yet.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($product->stockLogs->take(10) as $log)
                        <li class="py-2 flex justify-between gap-3">
                            <div>
                                <div class="font-medium text-slate-700 capitalize">{{ str_replace('_', ' ', $log->type) }}</div>
                                <div class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</div>
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

@endsection