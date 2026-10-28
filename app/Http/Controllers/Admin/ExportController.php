<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExportController extends Controller
{
    /**
     * Print-ready sales report.
     */
    public function sales(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $sales = Transaction::with('cashier')
            ->completed()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('created_at')
            ->get();

        return view('reports.print-sales', compact('sales', 'from', 'to'));
    }

    /**
     * Print-ready inventory summary.
     */
    public function inventory(): View
    {
        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();
        $totalValue = $products->sum(fn($p) => $p->stock_qty * $p->cost);

        return view('reports.print-inventory', compact('products', 'totalValue'));
    }

    /**
     * Print-ready staff activity report.
     */
    public function staff(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $staff = User::whereIn('role', ['cashier', 'stocker'])
            ->withCount(['transactions' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }])
            ->withSum(['transactions' => function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }], 'total')
            ->get();

        return view('reports.print-staff', compact('staff', 'from', 'to'));
    }

    /**
     * Print-ready financial report.
     */
    public function financial(Request $request): View
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $base = Transaction::completed()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        $revenue    = (clone $base)->sum('total');
        $count      = (clone $base)->count();
        $cashTotal  = (clone $base)->where('payment_method', 'cash')->sum('total');
        $gcashTotal = (clone $base)->where('payment_method', 'gcash')->sum('total');

        // Gross profit
        $profit = \App\Models\TransactionItem::whereHas('transaction', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                  ->where('status', 'completed');
            })
            ->selectRaw('SUM((unit_price - unit_cost) * qty) as total_profit')
            ->value('total_profit') ?? 0;

        return view('reports.print-financial', compact(
            'from', 'to', 'revenue', 'count', 'cashTotal', 'gcashTotal', 'profit'
        ));
    }
}