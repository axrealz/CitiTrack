<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VoidRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VoidController extends Controller
{
    public function index(Request $request): View
    {
        $query = VoidRequest::with(['transaction.cashier', 'requestedBy']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $voids = $query->latest()->paginate(15)->withQueryString();

        return view('admin.voids.index', compact('voids'));
    }

    public function show(VoidRequest $void): View
    {
        $void->load(['transaction.items.product', 'transaction.cashier', 'requestedBy', 'reviewedBy']);

        return view('admin.voids.show', compact('void'));
    }

    public function approve(Request $request, VoidRequest $void): RedirectResponse
    {
        if (! $void->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($request, $void) {
            $void->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Void the transaction
            $void->transaction->update(['status' => 'voided']);

            // Restore stock
            foreach ($void->transaction->items as $item) {
                $product = $item->product;
                if (! $product) continue;

                $before = $product->stock_qty;
                $product->increment('stock_qty', $item->qty);
                $after = $product->fresh()->stock_qty;

                \App\Models\StockLog::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'void_restock',
                    'qty'          => $item->qty,
                    'stock_before' => $before,
                    'stock_after'  => $after,
                    'reason'       => 'Voided transaction #' . $void->transaction->reference_no,
                ]);
            }

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'void.approved',
                'model_type' => VoidRequest::class,
                'model_id'   => $void->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('admin.voids.index')
            ->with('success', 'Void approved. Stock has been restored.');
    }

    public function reject(Request $request, VoidRequest $void): RedirectResponse
    {
        if (! $void->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $void->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'void.rejected',
            'model_type' => VoidRequest::class,
            'model_id'   => $void->id,
            'new_values' => ['reason' => $validated['rejection_reason']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.voids.index')
            ->with('success', 'Void request rejected.');
    }
}