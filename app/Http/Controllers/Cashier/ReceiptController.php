<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Transaction $transaction): View
    {
        // Cashiers can only view their own receipts
        if ($transaction->cashier_id !== auth()->id()) {
            abort(403);
        }

        $transaction->load(['items', 'cashier']);

        return view('cashier.receipt', compact('transaction'));
    }
}