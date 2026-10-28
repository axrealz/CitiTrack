@extends('layouts.stocker')

@section('title', 'Record Stock-In')

@section('content')

<x-page-header title="Record Stock-In" subtitle="Log a delivery, what it cost, and how much has been paid" />

<form method="POST" action="{{ route('stocker.stock-in.store') }}" class="max-w-3xl space-y-6"
      x-data="stockInForm({
          qty: @js((string) old('qty', 1)),
          unitCost: @js((string) old('unit_cost', '')),
          amountPaid: @js((string) old('amount_paid', '0')),
      })">
    @csrf
    <input type="hidden" name="submission_token" value="{{ $token }}">

    @error('submission_token')
        <x-alert type="error" :message="$message" />
    @enderror

    {{-- 1. What was delivered --}}
    <x-section-card title="Delivery">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-form-select
                    name="product_id"
                    label="Product"
                    :options="$products->pluck('name', 'id')->toArray()"
                    :value="old('product_id')"
                    required
                    placeholder="-- Select product --"
                />
            </div>

            <x-form-select
                name="supplier_id"
                label="Supplier (optional)"
                :options="$suppliers->pluck('name', 'id')->toArray()"
                :value="old('supplier_id')"
                placeholder="-- None --"
            />

            <x-form-input name="purchase_date" label="Purchase Date" type="date"
                          :value="old('purchase_date', now()->toDateString())"
                          max="{{ now()->toDateString() }}" required />

            <x-form-input name="qty" label="Quantity Received" type="number" min="1" max="100000" step="1"
                          x-model="qty" :value="old('qty', 1)" required />
        </div>
    </x-section-card>

    {{-- 2. What it cost --}}
    <x-section-card title="Purchase Cost">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-input name="unit_cost" label="Purchase Cost per Unit (₱)" type="number"
                          step="0.01" min="0.01" max="99999.99" inputmode="decimal"
                          x-model="unitCost" :value="old('unit_cost')"
                          hint="What you paid the supplier per unit, not the selling price." required />

            <div>
                <p class="label">Total Purchase Cost</p>
                <div class="input bg-slate-100 font-mono tabular-nums font-semibold text-slate-900" aria-live="polite"
                     x-text="peso(totalCents)">₱0.00</div>
                <p class="text-xs text-slate-600 mt-1">Quantity × cost per unit. Calculated automatically.</p>
            </div>
        </div>
    </x-section-card>

    {{-- 3. What was paid --}}
    <x-section-card title="Payment to Supplier">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-input name="amount_paid" label="Amount Paid (₱)" type="number"
                          step="0.01" min="0" inputmode="decimal"
                          x-model="amountPaid" :value="old('amount_paid', 0)"
                          hint="Enter 0 if nothing has been paid yet." required />

            <x-form-select
                name="payment_method"
                label="Payment Method"
                :options="\App\Models\StockLog::PAYMENT_METHODS"
                :value="old('payment_method')"
                placeholder="-- Select --"
                x-bind:required="paidCents > 0"
            />

            <div>
                <p class="label">Remaining Balance</p>
                <div class="input bg-slate-100 font-mono tabular-nums font-semibold text-slate-900" aria-live="polite"
                     x-text="peso(Math.max(balanceCents, 0))">₱0.00</div>
                <p class="text-xs text-red-600 mt-1" x-show="overpaid">
                    The amount paid is more than the total purchase cost.
                </p>
            </div>

            <div>
                <p class="label">Payment Status</p>
                <div class="input bg-slate-100 font-semibold text-slate-900" aria-live="polite" x-text="statusLabel">—</div>
                <p class="text-xs text-slate-600 mt-1">Worked out from the amount paid.</p>
            </div>

            <x-form-input name="reference_no" label="Reference No. (optional)" maxlength="100"
                          hint="Invoice, delivery receipt or transfer number." />

            <div class="md:col-span-2">
                <x-form-textarea name="notes" label="Notes (optional)" :value="old('notes')" />
            </div>
        </div>
    </x-section-card>

    <div>
        <x-confirm-submit
            label="Record Stock-In"
            class="btn-primary w-full sm:w-auto justify-center"
            variant="primary"
            title="Record this stock-in?"
            message="The received quantity will be added to the product's stock and the purchase will be saved."
            confirmLabel="Confirm Stock-In"
        >
            <dl class="space-y-1">
                <div class="flex justify-between gap-4"><dt>Product</dt><dd class="font-medium text-right" x-text="labels.product_id"></dd></div>
                <div class="flex justify-between gap-4"><dt>Supplier</dt><dd class="font-medium text-right" x-text="labels.supplier_id || 'None'"></dd></div>
                <div class="flex justify-between gap-4"><dt>Quantity</dt><dd class="font-medium" x-text="vals.qty"></dd></div>
                <div class="flex justify-between gap-4"><dt>Cost per unit</dt><dd class="font-medium" x-text="peso(cents(unitCost))"></dd></div>
                <div class="flex justify-between gap-4 border-t border-slate-200 pt-1"><dt class="font-semibold">Total cost</dt><dd class="font-semibold" x-text="peso(totalCents)"></dd></div>
                <div class="flex justify-between gap-4"><dt>Amount paid</dt><dd class="font-medium" x-text="peso(paidCents)"></dd></div>
                <div class="flex justify-between gap-4"><dt>Balance</dt><dd class="font-medium" x-text="peso(Math.max(balanceCents, 0))"></dd></div>
                <div class="flex justify-between gap-4"><dt>Status</dt><dd class="font-medium" x-text="statusLabel"></dd></div>
            </dl>
        </x-confirm-submit>
    </div>
</form>

<div class="mt-8">
    <x-section-card title="Recent Deliveries" subtitle="Last 10 stock-in entries">
        @if($recentLogs->isEmpty())
            <x-empty-state title="No deliveries yet" message="Record your first stock-in above." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2 text-left">Product</th>
                            <th class="py-2 text-left">Supplier</th>
                            <th class="py-2 text-right">Qty</th>
                            <th class="py-2 text-right">Total</th>
                            <th class="py-2 text-right">Paid</th>
                            <th class="py-2 text-right">Balance</th>
                            <th class="py-2 text-center">Status</th>
                            <th class="py-2 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentLogs as $log)
                            <tr>
                                <td class="py-2 text-slate-800 font-medium">{{ $log->product->name ?? '—' }}</td>
                                <td class="py-2 text-slate-600 text-xs">{{ $log->supplier->name ?? '—' }}</td>
                                <td class="py-2 text-right text-emerald-700 font-mono">+{{ $log->qty }}</td>
                                <td class="py-2 text-right whitespace-nowrap">
                                    @if($log->total_cost !== null) <x-money :amount="$log->total_cost" /> @else <span class="text-slate-500">—</span> @endif
                                </td>
                                <td class="py-2 text-right whitespace-nowrap">
                                    @if($log->amount_paid !== null) <x-money :amount="$log->amount_paid" /> @else <span class="text-slate-500">—</span> @endif
                                </td>
                                <td class="py-2 text-right whitespace-nowrap">
                                    @if($log->balance !== null) <x-money :amount="$log->balance" /> @else <span class="text-slate-500">—</span> @endif
                                </td>
                                <td class="py-2 text-center">
                                    @if($log->payment_status)
                                        <x-badge :color="['unpaid' => 'red', 'partial' => 'amber', 'paid' => 'green'][$log->payment_status] ?? 'slate'">
                                            {{ $log->payment_status_label }}
                                        </x-badge>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right text-xs text-slate-500 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, g:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-section-card>
</div>

@endsection