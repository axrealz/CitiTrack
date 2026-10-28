<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('items')
            ->where('cashier_id', auth()->id());

        // Date filter
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Search by reference number
        if ($search = $request->get('q')) {
            $query->where('reference_no', 'like', "%{$search}%");
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('cashier.sales-history', compact('transactions'));
    }

    public function show(Transaction $transaction): View
    {
        if ($transaction->cashier_id !== auth()->id()) {
            abort(403);
        }

        $transaction->load(['items.product', 'returns', 'voidRequest']);

        return view('cashier.sales-show', compact('transaction'));
    }
}