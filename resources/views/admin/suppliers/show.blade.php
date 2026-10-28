@extends('layouts.admin')

@section('title', $supplier->name)

@section('content')

<x-page-header :title="$supplier->name" :subtitle="$supplier->city ?? 'Supplier profile'" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 space-y-6">
        <x-section-card title="Contact Information">
            <dl class="space-y-3 text-sm">
                @if($supplier->contact_person)
                    <div>
                        <dt class="text-slate-500">Contact Person</dt>
                        <dd class="text-slate-800">{{ $supplier->contact_person }}</dd>
                    </div>
                @endif
                @if($supplier->phone)
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="text-slate-800">{{ $supplier->phone }}</dd>
                    </div>
                @endif
                @if($supplier->email)
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="text-slate-800">{{ $supplier->email }}</dd>
                    </div>
                @endif
                @if($supplier->address)
                    <div>
                        <dt class="text-slate-500">Address</dt>
                        <dd class="text-slate-800">{{ $supplier->address }}</dd>
                    </div>
                @endif
                @if($supplier->notes)
                    <div>
                        <dt class="text-slate-500">Notes</dt>
                        <dd class="text-slate-800 whitespace-pre-line">{{ $supplier->notes }}</dd>
                    </div>
                @endif
            </dl>
        </x-section-card>

        <x-section-card title="Actions">
            <div class="space-y-2">
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn-primary w-full justify-center">
                    Edit Supplier
                </a>
                <a href="{{ route('admin.suppliers.index') }}" class="btn-secondary w-full justify-center">
                    Back to list
                </a>
            </div>
        </x-section-card>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <x-section-card title="Products from this Supplier" :subtitle="$supplier->products->count() . ' item(s)'">
            @if($supplier->products->isEmpty())
                <p class="text-sm text-slate-500">No products linked to this supplier yet.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2 text-left">Product</th>
                            <th class="py-2 text-right">Price</th>
                            <th class="py-2 text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($supplier->products as $product)
                            <tr>
                                <td class="py-2">
                                    <a href="{{ route('admin.products.show', $product) }}"
                                       class="text-indigo-600 hover:text-indigo-800">{{ $product->name }}</a>
                                </td>
                                <td class="py-2 text-right"><x-money :amount="$product->price" /></td>
                                <td class="py-2 text-right">{{ $product->stock_qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-section-card>

        <x-section-card title="Recent Deliveries" subtitle="Last 10 stock-in entries">
            @if($supplier->stockLogs->isEmpty())
                <p class="text-sm text-slate-500">No deliveries recorded yet.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($supplier->stockLogs->sortByDesc('created_at')->take(10) as $log)
                        <li class="py-2 flex justify-between gap-3">
                            <div>
                                <div class="font-medium text-slate-700">{{ $log->product->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $log->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                            <div class="text-emerald-700 font-mono text-sm">+{{ $log->qty }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-section-card>

    </div>

</div>

@endsection