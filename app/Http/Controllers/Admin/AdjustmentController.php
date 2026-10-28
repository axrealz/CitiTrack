<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentRequest;
use App\Models\AuditLog;
use App\Models\StockLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdjustmentRequest::with(['product', 'requestedBy']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $adjustments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.adjustments.index', compact('adjustments'));
    }

    public function show(AdjustmentRequest $adjustment): View
    {
        $adjustment->load(['product', 'requestedBy', 'reviewedBy']);

        return view('admin.adjustments.show', compact('adjustment'));
    }

    public function approve(Request $request, AdjustmentRequest $adjustment): RedirectResponse
    {
        if (! $adjustment->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        DB::transaction(function () use ($request, $adjustment) {
            $product = $adjustment->product;

            $before = $product->stock_qty;
            $product->update(['stock_qty' => $adjustment->new_qty]);
            $after = $product->fresh()->stock_qty;

            StockLog::create([
                'product_id'   => $product->id,
                'user_id'      => auth()->id(),
                'type'         => 'adjustment',
                'qty'          => $adjustment->difference,
                'stock_before' => $before,
                'stock_after'  => $after,
                'reason'       => $adjustment->reason,
            ]);

            $adjustment->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            AuditLog::create([
                'user_id'    => auth()->id(),
                'action'     => 'adjustment.approved',
                'model_type' => AdjustmentRequest::class,
                'model_id'   => $adjustment->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('admin.adjustments.index')
            ->with('success', 'Adjustment approved. Stock has been updated.');
    }

    public function reject(Request $request, AdjustmentRequest $adjustment): RedirectResponse
    {
        if (! $adjustment->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $adjustment->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'adjustment.rejected',
            'model_type' => AdjustmentRequest::class,
            'model_id'   => $adjustment->id,
            'new_values' => ['reason' => $validated['rejection_reason']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.adjustments.index')
            ->with('success', 'Adjustment rejected.');
    }
}