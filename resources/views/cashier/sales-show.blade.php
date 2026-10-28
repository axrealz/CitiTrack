@extends('layouts.cashier')

@section('title', 'Transaction Details')

@section('content')

<x-page-header :title="$transaction->reference_no" :subtitle="$transaction->created_at->format('M d, Y g:i A')">
    <x-slot:actions>
        <a href="{{ route('cashier.receipt', $transaction) }}" target="_blank" class="btn-secondary">🖨 Print Receipt</a>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">

        <x-section-card title="Items">
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
                    @foreach($transaction->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->product_name }}</td>
                            <td class="py-2 text-right">{{ $item->qty }}</td>
                            <td class="py-2 text-right"><x-money :amount="$item->unit_price" /></td>
                            <td class="py-2 text-right font-medium"><x-money :amount="$item->subtotal" /></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="py-2 text-right text-slate-600">Subtotal</td>
                        <td class="py-2 text-right"><x-money :amount="$transaction->subtotal" /></td>
                    </tr>
                    @if($transaction->discount > 0)
                        <tr>
                            <td colspan="3" class="py-2 text-right text-slate-600">Discount</td>
                            <td class="py-2 text-right text-red-600">-<x-money :amount="$transaction->discount" /></td>
                        </tr>
                    @endif
                    <tr class="font-semibold">
                        <td colspan="3" class="py-2 text-right text-slate-800">Total</td>
                        <td class="py-2 text-right text-slate-900"><x-money :amount="$transaction->total" /></td>
                    </tr>
                </tfoot>
            </table>
        </x-section-card>

        @if($transaction->returns->isNotEmpty())
            <x-section-card title="Returns Processed">
                <table class="w-full text-sm">
                    <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-2 text-left">Item</th>
                            <th class="py-2 text-right">Qty</th>
                            <th class="py-2 text-right pr-4">Refund</th> 
                            <th class="py-2 text-left pl-4">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaction->returns as $r)
                            <tr>
                                <td class="py-2">{{ $r->product->name ?? '—' }}</td>
                                <td class="py-2 text-right">{{ $r->qty }}</td>
                                <td class="py-2 text-right pr-4 font-medium text-slate-700">
                                    <x-money :amount="$r->refund_amount" />
                                </td>
                                <td class="py-2 text-xs text-slate-500 pl-4 max-w-xs whitespace-normal break-words">
                                    {{ $r->reason }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-section-card>
        @endif

        @if($transaction->voidRequest)
            <x-section-card title="Void Request">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Status</dt>
                        <dd>
                            @php
                                $sc = ['pending' => 'amber', 'approved' => 'green', 'rejected' => 'red'];
                            @endphp
                            <x-badge :color="$sc[$transaction->voidRequest->status] ?? 'slate'">
                                {{ ucfirst($transaction->voidRequest->status) }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reason</dt>
                        <dd class="text-slate-800 mt-1">{{ $transaction->voidRequest->reason }}</dd>
                    </div>
                </dl>
            </x-section-card>
        @endif

    </div>

    <div class="space-y-6">

        <x-section-card title="Transaction">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @php
                            $sc = ['completed' => 'green', 'voided' => 'red', 'returned' => 'amber'];
                        @endphp
                        <x-badge :color="$sc[$transaction->status] ?? 'slate'">
                            {{ ucfirst($transaction->status) }}
                        </x-badge>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Payment</dt>
                    <dd class="capitalize">{{ $transaction->payment_method }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Amount Paid</dt>
                    <dd><x-money :amount="$transaction->amount_paid" /></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Change</dt>
                    <dd><x-money :amount="$transaction->change_due" /></dd>
                </div>
            </dl>
        </x-section-card>

        <x-section-card title="Actions">
            <div class="space-y-2">
                @if($transaction->status === 'completed'
                    && ! $transaction->voidRequest
                    && $transaction->created_at->diffInHours(now()) <= 24)
                    <a href="{{ route('cashier.returns.create', $transaction) }}"
                       class="btn-secondary w-full justify-center">Process Return</a>

                    <a href="{{ route('cashier.voids.create', $transaction) }}"
                       class="btn-danger w-full justify-center">Request Void</a>
                @endif

                @if($transaction->created_at->diffInHours(now()) > 24)
                    <p class="text-xs text-amber-700">
                        ⚠ 24-hour return window expired.
                    </p>
                @endif

                <a href="{{ route('cashier.sales-history') }}" class="btn-secondary w-full justify-center">
                    Back to history
                </a>
            </div>
        </x-section-card>

    </div>

</div>

@endsection