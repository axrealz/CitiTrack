@extends('layouts.cashier')

@section('title', 'Returns & Exchanges')

@section('content')

<x-page-header title="Returns & Exchanges" subtitle="Process item returns within 24 hours" />

@isset($transaction)
    <div class="card p-6 mb-6">
        <h3 class="font-semibold text-slate-800 mb-2">
            Returning items from: <span class="font-mono">{{ $transaction->reference_no }}</span>
        </h3>
        <p class="text-sm text-slate-500 mb-4">
            Original sale: {{ $transaction->created_at->format('M d, Y g:i A') }}
            ({{ $transaction->created_at->diffForHumans() }})
        </p>

        <form method="POST" action="{{ route('cashier.returns.store', $transaction) }}" class="space-y-4">
            @csrf

            <div class="space-y-3">
                <label class="label">Select items to return</label>
                @foreach($transaction->items as $item)
                    <div class="flex items-center gap-4 p-3 border border-slate-200 rounded-md">
                        <input type="checkbox" 
                               name="items[{{ $loop->index }}][id]" 
                               value="{{ $item->id }}" 
                               id="item-check-{{ $loop->index }}"
                               class="item-checkbox rounded border-slate-500 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        
                        <div class="flex-1">
                            <label for="item-check-{{ $loop->index }}" class="text-sm font-medium text-slate-800 cursor-pointer">
                                {{ $item->product_name }}
                            </label>
                            <p class="text-xs text-slate-500">{{ $item->qty }} sold @ ₱{{ number_format($item->unit_price, 2) }}</p>
                        </div>

                        <div class="w-24">
                            <label class="text-xs text-slate-500">Qty to return</label>
                            <input type="number" 
                                   name="items[{{ $loop->index }}][qty]" 
                                   min="1" 
                                   max="{{ $item->qty }}" 
                                   value="1" 
                                   disabled
                                   id="item-qty-{{ $loop->index }}"
                                   class="item-qty input mt-1">
                        </div>
                    </div>
                @endforeach
                @error('items') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">Type</label>
                    <select name="type" class="input" required>
                        <option value="return" @selected(old('type') === 'return')>Return (refund)</option>
                        <option value="exchange" @selected(old('type') === 'exchange')>Exchange</option>
                    </select>
                    @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Reason</label>
                <textarea name="reason" rows="3" class="input" required>{{ old('reason') }}</textarea>
                @error('reason') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <x-confirm-submit
                    label="Process Return"
                    class="btn-primary"
                    variant="primary"
                    title="Process this return?"
                    message="Returned items go back into stock and the sale is marked as returned."
                    :subject="$transaction->reference_no"
                    confirmLabel="Confirm Return"
                >
                    <dl class="space-y-1">
                        <div class="flex justify-between"><dt>Type</dt><dd class="font-medium" x-text="labels.type"></dd></div>
                        <div class="flex justify-between"><dt>Items selected</dt>
                            <dd class="font-medium" x-text="Object.keys(vals).filter(k => /^items\[\d+\]\[id\]$/.test(k)).length"></dd></div>
                    </dl>
                </x-confirm-submit>
                <a href="{{ route('cashier.returns.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endisset

<x-section-card title="Recent Transactions" subtitle="Sales from the last 7 days">
    @if($transactions->isEmpty())
        <x-empty-state title="No recent transactions" message="Returns can only be processed for sales from the last 7 days." />
    @else
        <table class="w-full text-sm">
            <thead class="text-xs uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="py-2 text-left">Reference</th>
                    <th class="py-2 text-left">Date</th>
                    <th class="py-2 text-left">Cashier</th>
                    <th class="py-2 text-right">Total</th>
                    <th class="py-2 text-center">Status</th>
                    <th class="py-2 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($transactions as $tx)
                    @php
                        $hoursSince = $tx->created_at->diffInHours(now());
                        $alreadyReturned = $tx->returns->isNotEmpty();
                        $eligible = $hoursSince <= 24 && $tx->status === 'completed' && !$alreadyReturned;
                    @endphp
                    <tr>
                        <td class="py-2 font-mono text-xs">{{ $tx->reference_no }}</td>
                        <td class="py-2 text-xs text-slate-500">{{ $tx->created_at->format('M d, g:i A') }}</td>
                        <td class="py-2 text-sm text-slate-600">{{ $tx->cashier->name ?? '—' }}</td>
                        <td class="py-2 text-right text-sm"><x-money :amount="$tx->total" /></td>
                        <td class="py-2 text-center">
                            @if($tx->status === 'returned' || $alreadyReturned)
                                <x-badge color="amber">Returned</x-badge>
                            @elseif($tx->status === 'voided')
                                <x-badge color="red">Voided</x-badge>
                            @elseif($hoursSince > 24)
                                <x-badge color="slate">Expired</x-badge>
                            @else
                                <x-badge color="green">Completed</x-badge>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @if($eligible)
                                <a href="{{ route('cashier.returns.create', $tx) }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Process
                                </a>
                            @else
                                <span class="text-slate-500 text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <x-pagination :paginator="$transactions" />
    @endif
</x-section-card>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', function() {
            const index = this.id.split('-').pop();
            const qtyInput = document.getElementById('item-qty-' + index);

            qtyInput.disabled = !this.checked;
        });
    });
});
</script>

@endsection