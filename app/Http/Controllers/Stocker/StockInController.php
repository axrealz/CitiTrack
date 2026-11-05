<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\UpdateStockInRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Supplier;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    /**
     * Show the "Update Stock-In" form for an existing stock-in record.
     */
    public function edit(StockLog $stockLog): View|RedirectResponse
    {
        abort_unless($stockLog->type === 'stock_in', 404);

        if ($stockLog->product?->trashed()) {
            return redirect()->route('stocker.shipment-logs')
                ->with('error', 'The product of this record was deleted, so the record can no longer be edited.');
        }

        // Active products, plus this record's own product even if it is inactive
        $products = Product::where(fn ($q) => $q->active()->orWhere('id', $stockLog->product_id))
            ->orderBy('name')
            ->get();

        // Active suppliers, plus this record's own supplier even if it was deactivated/deleted
        $suppliers = Supplier::withTrashed()
            ->where(fn ($q) => $q->where(fn ($a) => $a->where('is_active', true)->whereNull('deleted_at'))
                                 ->orWhere('id', $stockLog->supplier_id))
            ->orderBy('name')
            ->get();

        return view('stocker.stock-in-edit', [
            'log'       => $stockLog->load(['product', 'user']),
            'products'  => $products,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update an existing stock-in record.
     *
     * Stock is changed ONLY by the difference between the old and new quantity
     * (10 -> 15 adds 5, 10 -> 7 removes 3). The same stock_logs row is updated,
     * no new row is created. Everything runs in one transaction.
     * Submitting the same edit twice cannot change stock twice: the second time
     * the stored quantity already equals the new one, so the difference is 0.
     */
    public function update(UpdateStockInRequest $request, StockLog $stockLog): RedirectResponse
    {
        abort_unless($stockLog->type === 'stock_in', 404);

        $data   = $request->validated();
        $newQty = (int) $data['qty'];
        $calc   = StockLog::calculatePurchase($newQty, $data['unit_cost'], $data['amount_paid']);
        $newCost = number_format((float) $data['unit_cost'], 2, '.', '');

        $result = [];

        DB::transaction(function () use ($request, $stockLog, $data, $calc, $newQty, $newCost, &$result) {
            // Lock this record and re-read it, so we work with its latest values
            $log = StockLog::lockForUpdate()->findOrFail($stockLog->id);
            abort_unless($log->type === 'stock_in', 404);

            $oldProductId   = (int) $log->product_id;
            $newProductId   = (int) $data['product_id'];
            $oldQty         = (int) $log->qty;
            $productChanged = $oldProductId !== $newProductId;

            // Lock the product(s) in a fixed order so two edits can't deadlock
            $ids = array_values(array_unique([$oldProductId, $newProductId]));
            sort($ids);

            $products = Product::lockForUpdate()->whereIn('id', $ids)->orderBy('id')->get()->keyBy('id');

            if ($products->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'product_id' => 'That product no longer exists, so this record cannot be changed.',
                ]);
            }

            $oldProduct = $products[$oldProductId];
            $newProduct = $products[$newProductId];

            // What the record looked like before (for the Action Log)
            $before = [
                'product'        => $oldProduct->name,
                'supplier'       => $log->supplier?->name,
                'qty'            => $oldQty,
                'unit_cost'      => $log->unit_cost,
                'total_cost'     => $log->total_cost,
                'amount_paid'    => $log->amount_paid,
                'payment_method' => $log->payment_method,
                'payment_status' => $log->payment_status,
                'purchase_date'  => $log->purchase_date?->toDateString(),
                'reference_no'   => $log->reference_no,
                'notes'          => $log->reason,
            ];
            $costChanged = $log->unit_cost === null || (string) $log->unit_cost !== $newCost;

            /* ── Stock: apply only the difference ── */
            if (! $productChanged) {
                $diff = $newQty - $oldQty;

                if ($oldProduct->stock_qty + $diff < 0) {
                    throw ValidationException::withMessages([
                        'qty' => sprintf(
                            'Cannot change the quantity from %d to %d: that would remove %d unit(s) from stock, but only %d are in stock now (the rest were already sold or adjusted).',
                            $oldQty, $newQty, abs($diff), $oldProduct->stock_qty
                        ),
                    ]);
                }

                if ($diff !== 0) {
                    $oldProduct->increment('stock_qty', $diff);
                }

                $stockBefore = (int) $log->stock_before;   // history stays as recorded
                $adjustment  = $diff;
            } else {
                // Moving the record to another product: take the old quantity back
                // out of the old product and add the new quantity to the new one.
                if ($oldProduct->stock_qty - $oldQty < 0) {
                    throw ValidationException::withMessages([
                        'product_id' => sprintf(
                            'Cannot move this stock-in: %d unit(s) would be removed from "%s", but only %d are in stock now.',
                            $oldQty, $oldProduct->name, $oldProduct->stock_qty
                        ),
                    ]);
                }

                $oldProduct->decrement('stock_qty', $oldQty);

                $stockBefore = (int) $newProduct->stock_qty;
                $newProduct->increment('stock_qty', $newQty);
                $adjustment  = $newQty;
            }

            $stockAfter = $stockBefore + $newQty;

            /* ── Product cost: same rule as recording (latest stock-in sets the cost) ── */
            $newIsLatest = ! StockLog::where('type', 'stock_in')
                ->where('product_id', $newProductId)
                ->where('id', '>', $log->id)
                ->exists();

            if ($newIsLatest && ($productChanged || $costChanged)) {
                $newProduct->update(['cost' => $newCost]);
            }

            if ($productChanged) {
                $oldWasLatest = ! StockLog::where('type', 'stock_in')
                    ->where('product_id', $oldProductId)
                    ->where('id', '>', $log->id)
                    ->exists();

                if ($oldWasLatest) {
                    $previous = StockLog::where('type', 'stock_in')
                        ->where('product_id', $oldProductId)
                        ->where('id', '<', $log->id)
                        ->whereNotNull('unit_cost')
                        ->orderByDesc('id')
                        ->first();

                    if ($previous) {
                        $oldProduct->update(['cost' => $previous->unit_cost]);
                    }
                }
            }

            /* ── Update the SAME stock log row (token, recorder and type are kept) ── */
            $log->update([
                'product_id'     => $newProductId,
                'supplier_id'    => $data['supplier_id'] ?? null,
                'qty'            => $newQty,
                'stock_before'   => $stockBefore,
                'stock_after'    => $stockAfter,
                'unit_cost'      => $newCost,
                'reason'         => $data['notes'] ?? null,
                'purchase_date'  => $data['purchase_date'],
                'total_cost'     => $calc['total'],
                'amount_paid'    => $calc['paid'],
                'payment_method' => $calc['paid_cents'] > 0 ? ($data['payment_method'] ?? null) : null,
                'payment_status' => $calc['status'],
                'reference_no'   => $data['reference_no'] ?? null,
            ]);

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'stock.in.updated',
                'model_type' => StockLog::class,
                'model_id'   => $log->id,
                'old_values' => $before,
                'new_values' => [
                    'product'          => $newProduct->name,
                    'supplier'         => isset($data['supplier_id']) ? Supplier::withTrashed()->find($data['supplier_id'])?->name : null,
                    'qty'              => $newQty,
                    'unit_cost'        => $newCost,
                    'total_cost'       => $calc['total'],
                    'amount_paid'      => $calc['paid'],
                    'payment_method'   => $calc['paid_cents'] > 0 ? ($data['payment_method'] ?? null) : null,
                    'payment_status'   => $calc['status'],
                    'purchase_date'    => $data['purchase_date'],
                    'reference_no'     => $data['reference_no'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                    'stock_adjustment' => $adjustment,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $result = [
                'moved'      => $productChanged,
                'adjustment' => $adjustment,
                'old_name'   => $oldProduct->name,
                'old_qty'    => $oldQty,
                'new_name'   => $newProduct->name,
                'stock_now'  => (int) $newProduct->stock_qty,
            ];
        });

        $message = $result['moved']
            ? sprintf(
                'Stock-in updated and moved to "%s": %d unit(s) taken out of "%s" and %d added (now %d in stock).',
                $result['new_name'], $result['old_qty'], $result['old_name'], $result['adjustment'], $result['stock_now']
            )
            : sprintf(
                'Stock-in updated. Stock adjusted by %s (now %d in stock).',
                ($result['adjustment'] > 0 ? '+' : '') . $result['adjustment'],
                $result['stock_now']
            );

        return redirect()->route('stocker.shipment-logs')->with('success', $message);
    }
}