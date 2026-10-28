@extends('layouts.stocker')

@section('title', 'Request Stock Adjustment')

@section('content')

<x-page-header title="Request Stock Adjustment" subtitle="Correct stock when physical count doesn't match" />

<div class="max-w-2xl">
    <form method="POST" action="{{ route('stocker.adjustments.store') }}">
        @csrf

        <x-section-card title="Adjustment Details">
            <div class="space-y-4">

                <div>
                    <label class="label">Product</label>
                    <select name="product_id" id="product_id" class="input" required
                            onchange="updateCurrentQty()">
                        <option value="">-- Select product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-current="{{ $product->stock_qty }}"
                                    @selected(old('product_id') == $product->id)>
                                {{ $product->name }} (current: {{ $product->stock_qty }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Current Stock (system)</label>
                        <input type="number" id="current_qty" class="input bg-slate-100" readonly value="">
                    </div>

                    <x-form-input name="new_qty" label="New Quantity (counted)" type="number"
                                  min="0" :value="old('new_qty')" required
                                  hint="What you actually counted on the shelf" />
                </div>

                <div>
                    <label class="label">Reason</label>
                    <textarea name="reason" rows="4" class="input" required
                              placeholder="E.g., Physical count showed 3 fewer units due to damage...">{{ old('reason') }}</textarea>
                    @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded p-3 text-xs text-amber-800">
                    ⚠ This request will be sent to the admin for approval. Stock won't change until approved.
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Submit Request</button>
                    <a href="{{ route('stocker.adjustments.index') }}" class="btn-secondary">Cancel</a>
                </div>

            </div>
        </x-section-card>
    </form>
</div>

<script>
    function updateCurrentQty() {
        const select = document.getElementById('product_id');
        const selected = select.options[select.selectedIndex];
        const current = selected.getAttribute('data-current') ?? '';
        document.getElementById('current_qty').value = current;
    }
    document.addEventListener('DOMContentLoaded', updateCurrentQty);
</script>

@endsection