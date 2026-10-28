<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockInRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockInController extends Controller
{
    /**
     * Show the stock-in form and recent deliveries.
     */
    public function index(Request $request): View
    {
        $products  = Product::active()->orderBy('name')->get();
        $suppliers = Supplier::active()->orderBy('name')->get();

        // Recent deliveries
        $recentLogs = StockLog::with(['product', 'supplier'])
            ->where('type', 'stock_in')
            ->latest()
            ->take(10)
            ->get();

        // One-time token for this form. It is saved with the stock-in, and the
        // unique column stops the same form from adding stock twice.
        $token = (string) Str::uuid();

        return view('stocker.stock-in', compact('products', 'suppliers', 'recentLogs', 'token'));
    }

    /**
     * Record a stock-in entry (stock + purchase cost + supplier payment).
     */
    public function store(StockInRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Totals are always calculated here on the server.
        $calc = StockLog::calculatePurchase(
            (int) $validated['qty'],
            $validated['unit_cost'],
            $validated['amount_paid']
        );

        try {
            DB::transaction(function () use ($request, $validated, $calc) {
                // Lock the row so a sale at the same moment cannot disturb the numbers
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

                $before = $product->stock_qty;
                $product->increment('stock_qty', $validated['qty']);
                $after = $product->fresh()->stock_qty;

                // Latest purchase cost (this is the cost, not the selling price)
                $product->update(['cost' => $validated['unit_cost']]);

                StockLog::create([
                    'product_id'       => $product->id,
                    'supplier_id'      => $validated['supplier_id'] ?? null,
                    'user_id'          => auth()->id(),
                    'type'             => 'stock_in',
                    'qty'              => $validated['qty'],
                    'stock_before'     => $before,
                    'stock_after'      => $after,
                    'unit_cost'        => $validated['unit_cost'],
                    'reason'           => $validated['notes'] ?? null,
                    'purchase_date'    => $validated['purchase_date'],
                    'total_cost'       => $calc['total'],
                    'amount_paid'      => $calc['paid'],
                    'payment_method'   => $calc['paid_cents'] > 0 ? ($validated['payment_method'] ?? null) : null,
                    'payment_status'   => $calc['status'],
                    'reference_no'     => $validated['reference_no'] ?? null,
                    'submission_token' => $validated['submission_token'],
                ]);

                AuditLog::create([
                    'user_id'    => auth()->id(),
                    'action'     => 'stock.in',
                    'model_type' => Product::class,
                    'model_id'   => $product->id,
                    'new_values' => [
                        'product'        => $product->name,
                        'qty_added'      => $validated['qty'],
                        'stock_before'   => $before,
                        'stock_after'    => $after,
                        'unit_cost'      => $validated['unit_cost'],
                        'total_cost'     => $calc['total'],
                        'amount_paid'    => $calc['paid'],
                        'payment_status' => $calc['status'],
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            // Same form submitted twice: the whole transaction was rolled back,
            // so no stock was added a second time.
            return redirect()->route('stocker.stock-in.index')
                ->with('error', 'This stock-in was already recorded, so nothing was added again. Please check Recent Deliveries.');
        }

        $status = StockLog::PAYMENT_STATUSES[$calc['status']];

        return redirect()->route('stocker.stock-in.index')
            ->with('success', sprintf(
                'Stock-in recorded: +%d units. Total ₱%s, paid ₱%s, balance ₱%s (%s).',
                $validated['qty'],
                number_format((float) $calc['total'], 2),
                number_format((float) $calc['paid'], 2),
                number_format((float) $calc['balance'], 2),
                $status
            ));
    }
}