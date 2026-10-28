@extends('layouts.cashier')

@section('title', 'Request Void')

@section('content')

<x-page-header title="Request Void" :subtitle="$transaction->reference_no" />

<div class="max-w-2xl space-y-6">

    <x-section-card title="Transaction Summary">
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Reference</dt>
                <dd class="font-mono text-xs">{{ $transaction->reference_no }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Date</dt>
                <dd>{{ $transaction->created_at->format('M d, Y g:i A') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Total</dt>
                <dd class="font-medium"><x-money :amount="$transaction->total" /></dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Items</dt>
                <dd>{{ $transaction->items->count() }}</dd>
            </div>
        </dl>
    </x-section-card>

    <form method="POST" action="{{ route('cashier.voids.store', $transaction) }}">
        @csrf

        <x-section-card title="Reason for Void">
            <p class="text-sm text-slate-600 mb-3">
                Explain why this transaction should be voided. The admin will review and either approve or reject.
            </p>

            <textarea name="reason" rows="5" class="input" required
                      placeholder="E.g., Customer changed mind, duplicate entry, wrong item scanned...">{{ old('reason') }}</textarea>
            @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </x-section-card>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="btn-danger">Submit Void Request</button>
            <a href="{{ route('cashier.sales-history.show', $transaction) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

</div>

@endsection