@extends('layouts.admin')

@section('title', 'Void Request')

@section('content')

<x-page-header title="Void Request" :subtitle="'Reference: ' . ($void->transaction->reference_no ?? '—')" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <x-section-card title="Transaction Details">
            @if($void->transaction)
                <table class="w-full text-sm mb-4">
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-2 text-slate-500 w-40">Reference</td>
                            <td class="py-2 font-mono text-xs">{{ $void->transaction->reference_no }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Cashier</td>
                            <td class="py-2">{{ $void->transaction->cashier->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Date</td>
                            <td class="py-2">{{ $void->transaction->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Total</td>
                            <td class="py-2 font-medium"><x-money :amount="$void->transaction->total" /></td>
                        </tr>
                        <tr>
                            <td class="py-2 text-slate-500">Payment</td>
                            <td class="py-2 capitalize">{{ $void->transaction->payment_method }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="border-t border-slate-200 pt-4">
                    <p class="text-sm font-medium text-slate-700 mb-2">Items</p>
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-2 text-left">Item</th>
                                <th class="py-2 text-right">Qty</th>
                                <th class="py-2 text-right">Price</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($void->transaction->items as $item)
                                <tr>
                                    <td class="py-2">{{ $item->product_name }}</td>
                                    <td class="py-2 text-right">{{ $item->qty }}</td>
                                    <td class="py-2 text-right"><x-money :amount="$item->unit_price" /></td>
                                    <td class="py-2 text-right"><x-money :amount="$item->subtotal" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-slate-500">Transaction not found.</p>
            @endif
        </x-section-card>

        <x-section-card title="Void Request">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Requested by</dt>
                    <dd class="text-slate-800">{{ $void->requestedBy->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Submitted</dt>
                    <dd class="text-slate-800">{{ $void->created_at->format('M d, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Reason</dt>
                    <dd class="text-slate-800 whitespace-pre-line">{{ $void->reason }}</dd>
                </div>
                @if($void->reviewedBy)
                    <div>
                        <dt class="text-slate-500">Reviewed by</dt>
                        <dd class="text-slate-800">{{ $void->reviewedBy->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reviewed at</dt>
                        <dd class="text-slate-800">{{ $void->reviewed_at?->format('M d, Y g:i A') }}</dd>
                    </div>
                @endif
                @if($void->rejection_reason)
                    <div>
                        <dt class="text-slate-500">Rejection reason</dt>
                        <dd class="text-red-700">{{ $void->rejection_reason }}</dd>
                    </div>
                @endif
            </dl>
        </x-section-card>

    </div>

    <div class="lg:col-span-1 space-y-6">

        <x-section-card title="Status">
            @php
                $statusColors = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
            @endphp
            <div class="text-center py-4">
                <x-badge :color="$statusColors[$void->status] ?? 'slate'" size="md">
                    {{ ucfirst($void->status) }}
                </x-badge>
            </div>
        </x-section-card>

        @if($void->isPending())
            <x-section-card title="Decision">
                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.voids.approve', $void) }}" class="mb-4">
                    @csrf
                    <x-confirm-submit
                        label="Approve Void"
                        class="btn-primary w-full justify-center"
                        variant="primary"
                        title="Approve this void?"
                        message="Stock will be restored and the transaction will be marked as voided."
                        :subject="$void->transaction->reference_no ?? null"
                        confirmLabel="Confirm Void"
                    />
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('admin.voids.reject', $void) }}">
                    @csrf
                    <label class="label">Rejection reason</label>
                    <textarea name="rejection_reason" rows="3" class="input" required>{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <x-confirm-submit
                        label="Reject Void"
                        class="btn-danger w-full justify-center mt-3"
                        title="Reject this void request?"
                        message="The sale stays as it is. The cashier will see your reason."
                        :subject="$void->transaction->reference_no ?? null"
                        confirmLabel="Confirm Rejection"
                    />
                </form>
            </x-section-card>
        @endif

        <a href="{{ route('admin.voids.index') }}" class="btn-secondary w-full justify-center">
            Back to list
        </a>

    </div>

</div>

@endsection