@extends('layouts.cashier')

@section('title', 'Point of Sale')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Product search + grid --}}
    <div class="lg:col-span-2 space-y-4">

        <form method="GET" action="{{ route('cashier.pos') }}" class="card p-3">
            <div class="flex gap-3">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Search product by name or SKU..."
                    class="input flex-1"
                    autofocus
                >
                <button type="submit" class="btn-primary">Search</button>
                @if($search)
                    <a href="{{ route('cashier.pos') }}" class="btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        @if($products->isEmpty())
            <x-empty-state title="No products found" message="{{ $search ? 'Try a different search.' : 'No products with stock available.' }}" />
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($products as $product)
                    <form method="POST" action="{{ route('cashier.pos.add', $product) }}" class="flex">
                        @csrf
                        <input type="hidden" name="qty" value="1">
                        {{-- Every card has the same rows: name (2 lines reserved), SKU (1 line reserved),
                             then price + stock pinned to the bottom. A missing SKU keeps its space. --}}
                        <button type="submit"
                                class="flex w-full flex-col text-left card p-4 hover:border-emerald-400 hover:shadow transition">
                            <div class="pos-card-name font-medium text-slate-800 text-sm leading-tight"
                                 title="{{ $product->name }}">{{ $product->name }}</div>

                            <div class="mt-1 h-4 truncate text-xs font-mono text-slate-500"
                                 @if(filled($product->sku)) title="{{ $product->sku }}" @endif>
                                @if(filled($product->sku))
                                    {{ $product->sku }}
                                @else
                                    <span aria-hidden="true">&nbsp;</span>
                                @endif
                            </div>

                            <div class="mt-auto flex items-center justify-between gap-2 pt-2">
                                <span class="text-emerald-700 font-semibold">
                                    <x-money :amount="$product->price" />
                                </span>
                                <span class="whitespace-nowrap text-xs {{ $product->stock_qty <= $product->reorder_level ? 'text-amber-700' : 'text-slate-500' }}">
                                    {{ $product->stock_qty }} left
                                </span>
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Right: Cart --}}
    <aside class="space-y-4">

        <x-section-card title="Cart" :subtitle="$cart ? count($cart) . ' item(s)' : 'Empty'">

            @if(empty($cart))
                <div class="text-center py-8 text-slate-500">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="text-sm">No items yet. Click a product to add.</p>
                </div>
            @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($cart as $row)
                        <div class="border-b border-slate-100 pb-3 last:border-0">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div class="text-sm font-medium text-slate-800 leading-tight">
                                    {{ $row['product']->name }}
                                </div>
                                <form method="POST" action="{{ route('cashier.pos.remove', $row['product']) }}">
                                    @csrf
                                    <button type="submit" class="text-red-700 hover:text-red-900 text-xs" aria-label="Remove {{ $row['product']->name }}">✕</button>
                                </form>
                            </div>

                            <div class="flex justify-between items-center gap-2">
                                <form method="POST" action="{{ route('cashier.pos.update', $row['product']) }}" class="flex items-center gap-1">
                                    @csrf
                                    <input type="number" name="qty" value="{{ $row['qty'] }}"
                                           min="0" max="{{ $row['product']->stock_qty }}"
                                           class="w-16 border border-slate-500 rounded px-2 py-1 text-sm"
                                           aria-label="Quantity for {{ $row['product']->name }}">
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">Update</button>
                                </form>
                                <div class="font-medium text-slate-800 text-sm">
                                    <x-money :amount="$row['subtotal']" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </x-section-card>

        @if(!empty($cart))
            <x-section-card title="Checkout">
                <form method="POST" action="{{ route('cashier.pos.checkout') }}" class="space-y-3">
                    @csrf

                    <div class="flex justify-between items-center py-2 border-y border-slate-200">
                        <span class="text-sm text-slate-600">Total</span>
                        <span class="font-semibold"><x-money :amount="$subtotal" /></span>
                    </div>


                    <div>
                        <label class="label">Payment Method</label>
                        <select name="payment_method" class="input" required>
                            <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                            <option value="gcash" @selected(old('payment_method') === 'gcash')>GCash</option>
                        </select>
                        @error('payment_method') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Amount Paid (₱)</label>
                        <input type="number" name="amount_paid" value="{{ old('amount_paid') }}"
                               min="0" step="0.01" class="input" required>
                        @error('amount_paid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Notes (optional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="input" maxlength="500">
                    </div>

                    <x-confirm-submit
                        label="Complete Sale"
                        class="btn-primary w-full justify-center py-3 text-base"
                        variant="primary"
                        title="Complete this sale?"
                        message="Stock will be deducted and a receipt will be created."
                        confirmLabel="Confirm Sale"
                    >
                        <dl class="space-y-1">
                            <div class="flex justify-between"><dt>Total</dt><dd class="font-semibold"><x-money :amount="$subtotal" /></dd></div>
                            <div class="flex justify-between"><dt>Payment method</dt><dd class="font-medium" x-text="labels.payment_method"></dd></div>
                            <div class="flex justify-between"><dt>Amount paid</dt><dd class="font-medium" x-text="'₱' + Number(vals.amount_paid || 0).toFixed(2)"></dd></div>
                        </dl>
                    </x-confirm-submit>
                </form>

                <div class="mt-2 text-center">
                    <x-confirm-dialog
                        :action="route('cashier.pos.clear')"
                        buttonLabel="Clear cart"
                        buttonClass="text-xs text-red-700 hover:text-red-900"
                        title="Clear the cart?"
                        message="Every item will be removed from the current sale."
                        confirmLabel="Clear Cart"
                    />
                </div>
            </x-section-card>
        @endif

    </aside>
</div>

@endsection