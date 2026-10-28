<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustmentRequestRequest;
use App\Models\AdjustmentRequest;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdjustmentRequestController extends Controller
{
    /**
     * List this stocker's adjustment requests.
     */
    public function index(): View
    {
        $adjustments = AdjustmentRequest::with(['product', 'reviewedBy'])
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(15);

        return view('stocker.adjustments.index', compact('adjustments'));
    }

    /**
     * Show the adjustment request form.
     */
    public function create(): View
    {
        $products = Product::active()->orderBy('name')->get();

        return view('stocker.adjustments.create', compact('products'));
    }

    /**
     * Submit an adjustment request.
     */
    public function store(AdjustmentRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        $oldQty      = $product->stock_qty;
        $newQty      = $validated['new_qty'];
        $difference  = $newQty - $oldQty;

        $adjustment = AdjustmentRequest::create([
            'product_id'   => $product->id,
            'requested_by' => auth()->id(),
            'old_qty'      => $oldQty,
            'new_qty'      => $newQty,
            'difference'   => $difference,
            'reason'       => $validated['reason'],
            'status'       => 'pending',
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'adjustment.requested',
            'model_type' => AdjustmentRequest::class,
            'model_id'   => $adjustment->id,
            'new_values' => [
                'product'    => $product->name,
                'old_qty'    => $oldQty,
                'new_qty'    => $newQty,
                'difference' => $difference,
                'reason'     => $validated['reason'],
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('stocker.adjustments.index')
            ->with('success', 'Adjustment request submitted. Awaiting admin approval.');
    }
}