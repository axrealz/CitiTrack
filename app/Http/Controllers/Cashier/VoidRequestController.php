<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoidRequestRequest;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\VoidRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VoidRequestController extends Controller
{
    /**
     * List this cashier's void requests.
     */
    public function index(): View
    {
        $voids = VoidRequest::with('transaction')
            ->where('requested_by', auth()->id())
            ->latest()
            ->paginate(15);

        return view('cashier.voids.index', compact('voids'));
    }

    /**
     * Show the void request form.
     */
    public function create(Transaction $transaction): View
    {
        if ($transaction->cashier_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status !== 'completed') {
            abort(403, 'Only completed transactions can be voided.');
        }

        if ($transaction->voidRequest()->exists()) {
            abort(403, 'A void request already exists for this transaction.');
        }

        $transaction->load('items');

        return view('cashier.voids.create', compact('transaction'));
    }

    /**
     * Submit a void request.
     */
    public function store(VoidRequestRequest $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->cashier_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Only completed transactions can be voided.');
        }

        if ($transaction->voidRequest()->exists()) {
            return back()->with('error', 'A void request already exists.');
        }

        $voidRequest = VoidRequest::create([
            'transaction_id' => $transaction->id,
            'requested_by'   => auth()->id(),
            'reason'         => $request->validated()['reason'],
            'status'         => 'pending',
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'void.requested',
            'model_type' => VoidRequest::class,
            'model_id'   => $voidRequest->id,
            'new_values' => [
                'transaction' => $transaction->reference_no,
                'reason'      => $request->validated()['reason'],
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('cashier.voids.index')
            ->with('success', 'Void request submitted. Awaiting admin approval.');
    }
}