@extends('layouts.admin')

@section('title', 'Adjustment Request')

@section('content')

<x-page-header title="Adjustment Request" :subtitle="$adjustment->product->name ?? '—'" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <x-section-card title="Product">
            @if($adjustment->product)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Name</dt>
                        <dd class="text-slate-800 font-medium">{{ $adjustment->product->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Current stock (as stored)</dt>
                        <dd class="text-slate-800">{{ $adjustment->product->stock_qty }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Reorder level</dt>
                        <dd class="text-slate-800">{{ $adjustment->product->reorder_level }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-500">Product not found.</p>
            @endif
        </x-section-card>

        <x-section-card title="Request Details">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Requested by</dt>
                    <dd class="text-slate-800">{{ $adjustment->requestedBy->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Submitted</dt>
                    <dd class="text-slate-800">{{ $adjustment->created_at->format('M d, Y g:i A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Old quantity</dt>
                    <dd class="text-slate-800">{{ $adjustment->old_qty }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">New quantity</dt>
                    <dd class="text-slate-800 font-medium">{{ $adjustment->new_qty }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Difference</dt>
                    <dd class="font-mono {{ $adjustment->difference > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ $adjustment->difference > 0 ? '+' : '' }}{{ $adjustment->difference }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Reason</dt>
                    <dd class="text-slate-800 whitespace-pre-line">{{ $adjustment->reason }}</dd>
                </div>
                @if($adjustment->reviewedBy)
                    <div>
                        <dt class="text-slate-500">Reviewed by</dt>
                        <dd class="text-slate-800">{{ $adjustment->reviewedBy->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reviewed at</dt>
                        <dd class="text-slate-800">{{ $adjustment->reviewed_at?->format('M d, Y g:i A') }}</dd>
                    </div>
                @endif
                @if($adjustment->rejection_reason)
                    <div>
                        <dt class="text-slate-500">Rejection reason</dt>
                        <dd class="text-red-700">{{ $adjustment->rejection_reason }}</dd>
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
                <x-badge :color="$statusColors[$adjustment->status] ?? 'slate'" size="md">
                    {{ ucfirst($adjustment->status) }}
                </x-badge>
            </div>
        </x-section-card>

        @if($adjustment->isPending())
            <x-section-card title="Decision">
                <form method="POST" action="{{ route('admin.adjustments.approve', $adjustment) }}" class="mb-4">
                    @csrf
                    <x-confirm-submit
                        label="Approve Adjustment"
                        class="btn-primary w-full justify-center"
                        variant="primary"
                        title="Approve this adjustment?"
                        message="Product stock will be changed to the new quantity right away."
                        :subject="($adjustment->product->name ?? 'Product') . ': ' . $adjustment->old_qty . ' → ' . $adjustment->new_qty"
                        confirmLabel="Confirm Approval"
                    />
                </form>

                <form method="POST" action="{{ route('admin.adjustments.reject', $adjustment) }}">
                    @csrf
                    <label class="label">Rejection reason</label>
                    <textarea name="rejection_reason" rows="3" class="input" required>{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <x-confirm-submit
                        label="Reject Adjustment"
                        class="btn-danger w-full justify-center mt-3"
                        title="Reject this adjustment?"
                        message="The stocker will see your reason. Stock will not change."
                        :subject="$adjustment->product->name ?? null"
                        confirmLabel="Confirm Rejection"
                    />
                </form>
            </x-section-card>
        @endif

        <a href="{{ route('admin.adjustments.index') }}" class="btn-secondary w-full justify-center">
            Back to list
        </a>

    </div>

</div>

@endsection