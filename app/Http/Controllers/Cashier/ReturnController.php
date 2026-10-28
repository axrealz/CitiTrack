<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnRequest;
use App\Models\AuditLog;
use App\Models\ReturnItem;
use App\Models\StockLog;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['items', 'cashier', 'returns'])
            ->where('status', '!=', 'voided')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->paginate(15);

        return view('cashier.returns', compact('transactions'));
    }

    public function create(Transaction $transaction): View
    {
        $hoursSince = $transaction->created_at->diffInHours(now());

        if ($hoursSince > 24) {
            abort(403, 'Return window (24 hours) has expired.');
        }

        if ($transaction->status === 'voided' || $transaction->status === 'returned') {
            abort(403, 'This transaction is no longer eligible for returns.');
        }

        $transaction->load(['items.product', 'cashier']);

        return view('cashier.returns', [
            'transactions' => collect(),
            'transaction'  => $transaction,
        ]);
    }

    public function store(ReturnRequest $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->created_at->diffInHours(now()) > 24) {
            return back()->with('error', 'Return window (24 hours) has expired.');
        }

        if ($transaction->status === 'voided' || $transaction->status === 'returned') {
            return back()->with('error', 'This transaction is no longer eligible for returns.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $transaction, $validated) {
            $totalRefund = 0;

            foreach ($validated['items'] as $itemData) {
                // Skip items that were not checked (qty = 0)
                if ($itemData['qty'] < 1) {
                    continue;
                }

                $item = $transaction->items()->findOrFail($itemData['id']);
                $product = $item->product;

                $refundAmount = $item->unit_price * $itemData['qty'];
                $totalRefund += $refundAmount;

                ReturnItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $product->id,
                    'processed_by'   => auth()->id(),
                    'qty'            => $itemData['qty'],
                    'refund_amount'  => $refundAmount,
                    'type'           => $validated['type'],
                    'reason'         => $validated['reason'],
                ]);

                $before = $product->stock_qty;
                $product->increment('stock_qty', $itemData['qty']);
                $after = $product->fresh()->stock_qty;

                StockLog::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'return_restock',
                    'qty'          => $itemData['qty'],
                    'stock_before' => $before,
                    'stock_after'  => $after,
                    'reason'       => 'Return on ' . $transaction->reference_no,
                ]);
            }

            // UPDATED: If ANY item was returned, mark the whole transaction as returned
            if ($totalRefund > 0) {
                $transaction->update(['status' => 'returned']);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'return.processed',
                'model_type' => ReturnItem::class,
                'new_values' => [
                    'transaction'  => $transaction->reference_no,
                    'total_refund' => $totalRefund,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('cashier.returns.index')
            ->with('success', 'Return processed. Items restocked.');
    }
}